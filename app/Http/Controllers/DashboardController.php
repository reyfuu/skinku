<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $summary = $this->reports->summary($user);
        $poStatus = $this->reports->poStatusDistribution($user);
        $salesTrend = $this->reports->salesTrend('day', 14, $user);

        // Recent POs visible to this user.
        $recentPo = PurchaseOrder::query()
            ->when($user->isPartner(), fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->limit(8)
            ->get();

        // Low-stock alerts.
        $lowStock = Inventory::query()
            ->with('product', 'user')
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->when($user->isPartner(), fn ($q) => $q->where('user_id', $user->id))
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('user', 'summary', 'poStatus', 'salesTrend', 'recentPo', 'lowStock'));
    }
}
