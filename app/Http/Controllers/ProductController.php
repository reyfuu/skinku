<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'category']);

        $products = Product::query()
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $cat) => $query->where('category', $cat))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Product::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('products.index', compact('products', 'filters', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['image_path'] = $this->storeImage($request);
        $data['name'] = $data['name'];

        $product = Product::create($data);

        AuditService::log(
            action: 'create_product',
            targetType: 'product',
            targetId: $product->id,
            after: $product->only(['name', 'sku', 'price_distributor', 'price_reseller', 'price_retail', 'hq_stock', 'status']),
        );

        return back()->with('status', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateData($request, $product);

        $before = $product->only(['name', 'sku', 'price_distributor', 'price_reseller', 'price_retail', 'cogs', 'hq_stock', 'status']);

        if ($request->hasFile('image')) {
            $newPath = $this->storeImage($request);
            if ($newPath) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $data['image_path'] = $newPath;
            }
        }

        $product->update($data);

        AuditService::log(
            action: 'update_product',
            targetType: 'product',
            targetId: $product->id,
            before: $before,
            after: $product->only(['name', 'sku', 'price_distributor', 'price_reseller', 'price_retail', 'cogs', 'hq_stock', 'status']),
        );

        return back()->with('status', "Produk {$product->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $product->status = Product::STATUS_DELETED;
        $product->save();
        $product->delete(); // soft delete

        AuditService::log(
            action: 'delete_product',
            targetType: 'product',
            targetId: $product->id,
            before: ['status' => 'active'],
            after: ['status' => Product::STATUS_DELETED],
        );

        return back()->with('status', "Produk {$product->name} berhasil dihapus (soft delete).");
    }

    private function validateData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_distributor' => ['required', 'numeric', 'min:0'],
            'price_reseller' => ['required', 'numeric', 'min:0'],
            'price_retail' => ['required', 'numeric', 'min:0'],
            'cogs' => ['required', 'numeric', 'min:0'],
            'hq_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    /** Safely store an uploaded product image on the public disk. */
    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('products', 'public');
    }
}
