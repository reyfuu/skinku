<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\AuditService;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['status', 'q']);

        $orders = PurchaseOrder::query()
            ->with('user')
            ->when($user->isPartner(), fn ($q) => $q->where('user_id', $user->id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('po_number', 'like', "%{$term}%")
                        ->orWhere('company_name', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase_orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => PurchaseOrder::STATUSES,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isPartner(), 403, 'Hanya distributor/reseller yang dapat membuat PO.');

        $priceField = $user->priceField();
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('purchase_orders.create', compact('products', 'priceField', 'user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isPartner(), 403);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:0'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $po = $this->service->createForPartner(
            buyer: $user,
            lines: $data['items'],
            shippingAddress: $data['shipping_address'] ?? null,
            notes: $data['notes'] ?? null,
        );

        return redirect()->route('purchase-orders.show', $po)
            ->with('status', "Purchase Order {$po->po_number} berhasil diajukan.");
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        $user = $request->user();
        if ($user->isPartner() && $purchaseOrder->user_id !== $user->id) {
            abort(403, 'Anda hanya dapat melihat PO milik Anda sendiri.');
        }

        $purchaseOrder->load('items', 'user');
        $nextStatuses = PurchaseOrder::TRANSITIONS[$purchaseOrder->status] ?? [];

        return view('purchase_orders.show', compact('purchaseOrder', 'nextStatuses', 'user'));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(PurchaseOrder::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->service->updateStatus($purchaseOrder, $data['status'], $data['notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', "Status PO {$purchaseOrder->po_number} diperbarui menjadi {$data['status']}.");
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = $request->user();
        // Partners may cancel their own PO only while still pending/draft.
        if ($user->isPartner()) {
            if ($purchaseOrder->user_id !== $user->id) {
                abort(403);
            }
            if (! in_array($purchaseOrder->status, [PurchaseOrder::STATUS_PENDING, PurchaseOrder::STATUS_DRAFT], true)) {
                return back()->withErrors(['status' => 'PO hanya dapat dibatalkan saat masih pending.']);
            }
        }

        try {
            $this->service->cancel($purchaseOrder, $request->input('notes'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', "PO {$purchaseOrder->po_number} dibatalkan.");
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isManagement(), 403, 'Hanya admin/super_admin yang dapat menghapus PO.');

        $purchaseOrder->status = PurchaseOrder::STATUS_DELETED;
        $purchaseOrder->deleted_by = $user->id;
        $purchaseOrder->save();
        $purchaseOrder->delete(); // soft delete

        AuditService::log(
            action: 'delete_po',
            targetType: 'purchase_order',
            targetId: $purchaseOrder->id,
            after: ['status' => PurchaseOrder::STATUS_DELETED],
        );

        return redirect()->route('purchase-orders.index')
            ->with('status', "PO {$purchaseOrder->po_number} berhasil dihapus (soft delete).");
    }
}
