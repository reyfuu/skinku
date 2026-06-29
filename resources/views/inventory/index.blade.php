@extends('layouts.app')
@section('title', 'Pemantauan Stok')
@section('heading', 'Pemantauan Inventori')

@section('content')
@php
    $u = $user;
    $movementTypes = [\App\Models\StockMovement::TYPE_IN, \App\Models\StockMovement::TYPE_OUT, \App\Models\StockMovement::TYPE_ADJUSTMENT];
@endphp

@if($u->canDo('manage_hq_stock'))
    <div class="bg-white rounded-2xl border border-stone-200 p-6 mb-6">
        <h3 class="text-sm font-bold text-stone-800 mb-4">Pemantauan Stok (10 Produk Stok Pusat Terendah)</h3>
        <div id="inventoryChart" class="w-full h-80"></div>
    </div>
@else
    <div class="bg-white rounded-2xl border border-stone-200 p-6 mb-6">
        <h3 class="text-sm font-bold text-stone-800 mb-4">Pemantauan Stok (10 Produk Toko Terendah)</h3>
        <div id="inventoryChart" class="w-full h-80"></div>
    </div>
@endif

@if($u->canDo('manage_hq_stock'))
    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-stone-100 text-sm font-bold text-stone-800">Stok Pusat (HQ)</div>
        <table class="w-full text-xs">
            <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
                <tr><th class="text-left px-4 py-2">Produk</th><th class="text-left">SKU</th><th class="text-right">Stok</th><th class="text-right px-4 w-72">Penyesuaian</th></tr>
            </thead>
            <tbody>
                @forelse($hqProducts as $p)
                    <tr class="border-t border-stone-100">
                        <td class="px-4 py-3 font-semibold text-stone-800">{{ $p->name }}</td>
                        <td class="text-stone-500">{{ $p->sku }}</td>
                        <td class="text-right font-bold {{ $p->hq_stock <= 0 ? 'text-rose-600' : 'text-stone-800' }}">{{ $p->hq_stock }}</td>
                        <td class="px-4 py-2">
                            <form method="POST" action="{{ route('inventory.hq-adjust') }}" class="flex gap-1 justify-end items-center">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                <select name="type" class="px-2 py-1 border border-stone-300 rounded text-[11px]">
                                    @foreach($movementTypes as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                                </select>
                                <input type="number" name="quantity" min="1" value="1" class="w-16 px-2 py-1 border border-stone-300 rounded text-center text-[11px]">
                                <button class="px-3 py-1 bg-red-600 text-white rounded text-[11px]">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-stone-400">Belum ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

<div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-stone-100 text-sm font-bold text-stone-800">{{ $u->isPartner() ? 'Stok Saya' : 'Stok Mitra' }}</div>
    <table class="w-full text-xs">
        <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
            <tr>
                @if(!$u->isPartner())<th class="text-left px-4 py-2">Mitra</th>@endif
                <th class="text-left px-4 py-2">Produk</th><th class="text-right">Qty</th><th class="text-right">Min</th><th class="text-right px-4 w-72">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partnerStock as $line)
                <tr class="border-t border-stone-100 {{ $line->isLow() ? 'bg-rose-50/40' : '' }}">
                    @if(!$u->isPartner())<td class="px-4 py-3 text-stone-600">{{ $line->user->company_name ?? ($line->user->fullname ?? '-') }}</td>@endif
                    <td class="px-4 py-3 font-semibold text-stone-800">{{ $line->product->name ?? 'Produk' }}</td>
                    <td class="text-right font-bold {{ $line->isLow() ? 'text-rose-600' : 'text-stone-800' }}">{{ $line->quantity }}</td>
                    <td class="text-right text-stone-500">{{ $line->minimum_stock }}</td>
                    <td class="px-4 py-2">
                        <form method="POST" action="{{ route('inventory.partner-adjust') }}" class="flex gap-1 justify-end items-center">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $line->user_id }}">
                            <input type="hidden" name="product_id" value="{{ $line->product_id }}">
                            <select name="type" class="px-2 py-1 border border-stone-300 rounded text-[11px]">
                                @foreach($movementTypes as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                            </select>
                            <input type="number" name="quantity" min="1" value="1" class="w-14 px-2 py-1 border border-stone-300 rounded text-center text-[11px]">
                            <button class="px-3 py-1 bg-red-600 text-white rounded text-[11px]">OK</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-stone-400">Belum ada data stok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $partnerStock->links() }}</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categories = @json($chartCategories ?? []);
        const seriesData = @json($chartSeries ?? []);
        const colors = @json($chartColors ?? []);

        if (categories.length === 0 || seriesData.length === 0) return;

        const options = {
            series: seriesData,
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: colors,
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    borderRadius: 4
                },
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: {
                categories: categories,
                labels: { style: { colors: '#78716c', fontSize: '10px' } }
            },
            yaxis: {
                labels: { style: { colors: '#78716c', fontSize: '10px' } }
            },
            fill: { opacity: 1 },
            tooltip: {
                y: { formatter: function (val) { return val + " unit" } }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        const chart = new ApexCharts(document.querySelector("#inventoryChart"), options);
        chart.render();
    });
</script>
@endpush
