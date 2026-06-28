@extends('layouts.app')
@section('title', 'Stock Movement')
@section('heading', 'Riwayat Pergerakan Stok')

@section('content')
<div class="bg-white rounded-2xl border border-stone-200 p-6 mb-6 relative">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h3 id="chartTitle" class="text-sm font-bold text-stone-800">Grafik Pergerakan Stok</h3>
        <div class="flex items-center gap-2">
            {{-- Period toggle buttons --}}
            <div class="flex rounded-lg overflow-hidden border border-stone-200 text-xs font-semibold">
                <button data-period="weekly"  class="px-3 py-1.5 bg-stone-100 text-stone-700 hover:bg-stone-200 transition-colors">Mingguan</button>
                <button data-period="monthly" class="px-3 py-1.5 bg-red-600 text-white transition-colors">Bulanan</button>
                <button data-period="yearly"  class="px-3 py-1.5 bg-stone-100 text-stone-700 hover:bg-stone-200 transition-colors">Tahunan</button>
            </div>
            {{-- Month dropdown (visible only when period=monthly) --}}
            <select id="monthSelect" class="px-3 py-1.5 text-xs border border-stone-200 rounded-lg">
                @foreach($months as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <span id="chartLoading" class="hidden text-xs text-stone-400 animate-pulse">Memuat…</span>
        </div>
    </div>
    <div id="stockChart" class="w-full h-80"></div>
    <div id="chartEmpty" class="hidden absolute inset-0 flex flex-col items-center justify-center text-stone-400">
        <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="text-sm font-medium">Tidak ada data pergerakan stok</p>
        <p class="text-xs mt-1">di periode yang dipilih.</p>
    </div>
</div>

<div class="flex items-center justify-between mb-3 mt-8">
    <h3 class="text-sm font-bold text-stone-800">Rincian Data</h3>
    <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari produk/SKU…" class="px-3 py-2 text-sm border border-stone-300 rounded-lg w-56" onkeydown="if(event.key === 'Enter'){ this.form.submit(); }">
        <select name="type" class="px-3 py-2 text-sm border border-stone-300 rounded-lg" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            @foreach($types as $t)<option value="{{ $t }}" @selected(($filters['type'] ?? '')===$t)>{{ $t }}</option>@endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
            <tr>
                <th class="text-left px-4 py-3">Waktu</th>
                <th class="text-left">Produk</th>
                <th class="text-left">Pemilik</th>
                <th class="text-left">Tipe</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Sebelum</th>
                <th class="text-right">Sesudah</th>
                <th class="text-left px-4">Referensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $m)
                @php
                    $badge = match($m->movement_type) {
                        'IN','PO_FULFILLMENT' => 'bg-emerald-100 text-emerald-700',
                        'OUT' => 'bg-rose-100 text-rose-700',
                        default => 'bg-stone-100 text-stone-700',
                    };
                @endphp
                <tr class="border-t border-stone-100 hover:bg-stone-50">
                    <td class="px-4 py-2 text-stone-500">{{ $m->created_at?->format('d M Y H:i') }}</td>
                    <td class="font-semibold text-stone-800">{{ $m->product->name ?? '-' }}</td>
                    <td class="text-stone-500">{{ $m->user->company_name ?? ($m->user->fullname ?? 'HQ / Pusat') }}</td>
                    <td><span class="px-2 py-0.5 rounded-full text-[10px] {{ $badge }}">{{ $m->movement_type }}</span></td>
                    <td class="text-right font-bold">{{ $m->quantity }}</td>
                    <td class="text-right text-stone-400">{{ $m->before_qty }}</td>
                    <td class="text-right text-stone-700">{{ $m->after_qty }}</td>
                    <td class="px-4 text-stone-500">{{ $m->notes ?? ($m->reference_type ? $m->reference_type.' #'.$m->reference_id : '-') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-6 text-center text-stone-400">Belum ada pergerakan stok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $movements->links() }}</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const CHART_URL = "{{ route('stock-movements.chart-data') }}";
    const months    = @json($months);

    // ── State ──────────────────────────────────────────────────────────────
    let chart       = null;
    let currentPeriod = 'monthly';
    let currentMonth  = Object.keys(months)[0]; // most recent month

    // ── DOM ────────────────────────────────────────────────────────────────
    const periodBtns   = document.querySelectorAll('[data-period]');
    const monthSelect  = document.getElementById('monthSelect');
    const chartTitle   = document.getElementById('chartTitle');
    const chartLoading = document.getElementById('chartLoading');

    // ── Init chart skeleton ────────────────────────────────────────────────
    chart = new ApexCharts(document.querySelector('#stockChart'), {
        series: [],
        chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true, speed: 400 } },
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: { categories: [], labels: { style: { colors: '#78716c', fontSize: '10px' } } },
        yaxis: { labels: { style: { colors: '#78716c', fontSize: '10px' } } },
        fill: { opacity: 1 },
        tooltip: { y: { formatter: val => val + ' qty' } },
        legend: { position: 'top', horizontalAlign: 'right' },
        noData: { text: 'Tidak ada data untuk periode ini.' },
    });
    chart.render();

    // ── Fetch data via AJAX ────────────────────────────────────────────────
    const emptyEl = document.getElementById('chartEmpty');

    async function fetchChart() {
        chartLoading.classList.remove('hidden');
        emptyEl.classList.add('hidden');

        const params = new URLSearchParams({ period: currentPeriod });
        if (currentPeriod === 'monthly') params.append('month', currentMonth);

        const label = currentPeriod === 'weekly' ? '7 Hari Terakhir'
                    : currentPeriod === 'yearly' ? '12 Bulan Terakhir'
                    : 'Bulan ' + (months[currentMonth] ?? currentMonth);
        chartTitle.textContent = 'Grafik Pergerakan Stok — ' + label;

        try {
            const res  = await fetch(CHART_URL + '?' + params);
            const data = await res.json();

            if (data.empty) {
                chart.updateSeries([], false);
                chart.updateOptions({ xaxis: { categories: [] } }, false, false);
                emptyEl.classList.remove('hidden');
            } else {
                // Auto-fit column width: fewer bars → wider columns
                const count = data.categories.length;
                const colW  = count <= 7 ? '40%' : count <= 14 ? '55%' : '70%';
                chart.updateOptions({
                    xaxis: { categories: data.categories },
                    plotOptions: { bar: { columnWidth: colW, borderRadius: 4 } },
                }, false, false);
                chart.updateSeries(data.series, true);
            }
        } catch (e) {
            console.error('Chart fetch error:', e);
        } finally {
            chartLoading.classList.add('hidden');
        }
    }

    // ── Event: period toggle buttons ───────────────────────────────────────
    periodBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            periodBtns.forEach(b => b.classList.remove('bg-red-600', 'text-white'));
            periodBtns.forEach(b => b.classList.add('bg-stone-100', 'text-stone-700'));
            this.classList.add('bg-red-600', 'text-white');
            this.classList.remove('bg-stone-100', 'text-stone-700');

            currentPeriod = this.dataset.period;
            monthSelect.classList.toggle('hidden', currentPeriod !== 'monthly');
            fetchChart();
        });
    });

    // ── Event: month dropdown ──────────────────────────────────────────────
    monthSelect.addEventListener('change', function () {
        currentMonth = this.value;
        fetchChart();
    });

    // ── Initial load ───────────────────────────────────────────────────────
    fetchChart();
});
</script>
@endpush
