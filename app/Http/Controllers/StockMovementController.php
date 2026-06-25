<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['type', 'q']);

        $movements = StockMovement::query()
            ->with('product', 'user')
            ->when($user->isPartner(), fn ($q) => $q->where('user_id', $user->id))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('movement_type', $t))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('stock_movements.index', [
            'movements' => $movements,
            'filters' => $filters,
            'types' => StockMovement::TYPES,
        ]);
    }
}
