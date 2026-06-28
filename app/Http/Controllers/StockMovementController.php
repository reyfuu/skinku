<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Carbon\Carbon;
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

        // Build months list (last 12) for the filter dropdown
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $d = now()->subMonthsNoOverflow($i);
            $months[$d->format('Y-m')] = $d->translatedFormat('F Y');
        }

        return view('stock_movements.index', [
            'movements' => $movements,
            'filters'   => $filters,
            'types'     => StockMovement::TYPES,
            'months'    => $months,
        ]);
    }

    /**
     * AJAX endpoint: returns chart series + categories for the requested filter.
     * Query params:
     *   period = weekly | monthly | yearly
     *   month  = YYYY-MM (used when period=monthly, defaults to current month)
     */
    public function chartData(Request $request)
    {
        $user      = $request->user();
        $period    = $request->input('period', 'monthly');
        $isPartner = $user->isPartner();
        $month     = $request->input('month', now()->format('Y-m'));

        [$start, $end, $format, $labelFn] = $this->periodConfig($period, $month);

        $rows = StockMovement::query()
            ->when($isPartner, fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("$format as unit, movement_type, user_id, SUM(quantity) as total")
            ->groupBy('unit', 'movement_type', 'user_id')
            ->get();

        // Build sparse index: only units that have data
        $unitOrder = $rows->pluck('unit')->unique()->sort()->values();
        $indexMap  = $unitOrder->flip()->toArray();
        $len       = $unitOrder->count();

        $categories = $unitOrder->map($labelFn)->toArray();

        $hq_in  = array_fill(0, $len, 0);
        $hq_out = array_fill(0, $len, 0);
        $p_in   = array_fill(0, $len, 0);
        $p_out  = array_fill(0, $len, 0);

        foreach ($rows as $row) {
            $idx = $indexMap[$row->unit] ?? null;
            if ($idx === null) continue;

            $isHq  = is_null($row->user_id);
            $isIn  = in_array($row->movement_type, [StockMovement::TYPE_IN, StockMovement::TYPE_PO_FULFILLMENT]);
            $isOut = $row->movement_type === StockMovement::TYPE_OUT;

            if ($isHq && $isIn)    $hq_in[$idx]  += (int) $row->total;
            if ($isHq && $isOut)   $hq_out[$idx] += (int) $row->total;
            if (!$isHq && $isIn)   $p_in[$idx]   += (int) $row->total;
            if (!$isHq && $isOut)  $p_out[$idx]  += (int) $row->total;
        }

        $series = [];
        if (!$isPartner) {
            $series[] = ['name' => 'HQ - Masuk',  'data' => $hq_in,  'color' => '#10b981'];
            $series[] = ['name' => 'HQ - Keluar', 'data' => $hq_out, 'color' => '#f43f5e'];
        }
        $series[] = ['name' => 'Dist/Reseller - Masuk',  'data' => $p_in,  'color' => '#3b82f6'];
        $series[] = ['name' => 'Dist/Reseller - Keluar', 'data' => $p_out, 'color' => '#f59e0b'];

        return response()->json([
            'categories' => $categories,
            'series'     => $series,
            'empty'      => $len === 0,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Returns [start, end, SQL_format_expr, label_closure]
     * Label closure receives a raw unit string and returns a human-readable label.
     */
    private function periodConfig(string $period, string $ym): array
    {
        return match ($period) {
            'weekly' => [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
                'DATE(created_at)',
                fn ($d) => Carbon::parse($d)->translatedFormat('D, d M'),
            ],
            'yearly' => [
                now()->subMonthsNoOverflow(11)->startOfMonth(),
                now()->endOfMonth(),
                "DATE_FORMAT(created_at, '%Y-%m')",
                fn ($m) => Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y'),
            ],
            default => [
                Carbon::createFromFormat('Y-m', $ym)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $ym)->endOfMonth(),
                'DATE(created_at)',
                fn ($d) => Carbon::parse($d)->format('d M'),
            ],
        };
    }
}
