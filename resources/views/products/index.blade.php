@extends('layouts.app')
@section('title', 'Manajemen Produk')
@section('heading', 'Manajemen Produk')

@section('content')
<div class="flex justify-between items-center mb-4">
    <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama/SKU…" class="px-3 py-2 text-sm border border-stone-300 rounded-lg w-56">
        <select name="status" class="px-3 py-2 text-sm border border-stone-300 rounded-lg">
            <option value="">Semua Status</option>
            @foreach(['active','inactive','deleted'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '')===$s)>{{ $s }}</option>@endforeach
        </select>
        <button class="px-4 py-2 text-sm bg-stone-200 rounded-lg hover:bg-stone-300">Filter</button>
    </form>
    <button onclick="openProduct()" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">+ Tambah Produk</button>
</div>

<div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
            <tr>
                <th class="text-left px-4 py-3">Produk</th>
                <th class="text-left">SKU</th>
                <th class="text-left">Kategori</th>
                <th class="text-right">Distributor</th>
                <th class="text-right">Reseller</th>
                <th class="text-right">Retail</th>
                <th class="text-right">HPP</th>
                <th class="text-right">Stok Pusat</th>
                <th class="text-left">Status</th>
                <th class="text-right px-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $p)
                <tr class="border-t border-stone-100 hover:bg-stone-50">
                    <td class="px-4 py-3 font-semibold text-stone-800 flex items-center gap-2">
                        @if($p->imageUrl())<img src="{{ $p->imageUrl() }}" class="w-8 h-8 rounded object-cover">@else<span class="w-8 h-8 rounded bg-stone-100 flex items-center justify-center">{{ $p->image ?: '🧴' }}</span>@endif
                        {{ $p->name }}
                    </td>
                    <td class="text-stone-600">{{ $p->sku }}</td>
                    <td class="text-stone-600">{{ $p->category ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($p->price_distributor, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p->price_reseller, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($p->price_retail, 0, ',', '.') }}</td>
                    <td class="text-right text-stone-500">Rp {{ number_format($p->cogs, 0, ',', '.') }}</td>
                    <td class="text-right font-bold {{ $p->hq_stock <= 0 ? 'text-rose-600' : 'text-stone-800' }}">{{ $p->hq_stock }}</td>
                    <td><span class="px-2 py-0.5 rounded-full text-[10px] {{ $p->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600' }}">{{ $p->status }}</span></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($p->status !== 'deleted')
                            <button class="text-stone-500 hover:text-stone-900 font-semibold"
                                onclick='openProduct({{ json_encode($p->only(["id","name","sku","category","description","price_distributor","price_reseller","price_retail","cogs","hq_stock","status"])) }})'>Edit</button>
                            <form method="POST" action="{{ route('products.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus produk ini (soft delete)?')">
                                @csrf @method('DELETE')
                                <button class="ml-2 text-rose-600 hover:text-rose-800 font-semibold">Hapus</button>
                            </form>
                        @else <span class="text-stone-400">—</span> @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-4 py-6 text-center text-stone-400">Belum ada produk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $products->links() }}</div>

{{-- Product modal (create + edit) --}}
<div id="productModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="productModalTitle" class="text-sm font-bold text-stone-900">Tambah Produk</h3>
            <button onclick="toggleModal('productModal')" class="text-stone-400 hover:text-stone-700">✕</button>
        </div>
        <form method="POST" id="productForm" enctype="multipart/form-data" action="{{ route('products.store') }}" class="grid grid-cols-2 gap-3 text-sm">
            @csrf
            <input type="hidden" name="_method" id="productMethod" value="POST">
            <div class="col-span-2"><label class="block text-xs font-semibold mb-1">Nama *</label><input name="name" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">SKU *</label><input name="sku" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Kategori</label><input name="category" class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Harga Distributor *</label><input type="number" step="0.01" name="price_distributor" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Harga Reseller *</label><input type="number" step="0.01" name="price_reseller" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Harga Retail *</label><input type="number" step="0.01" name="price_retail" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">HPP / COGS *</label><input type="number" step="0.01" name="cogs" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Stok Pusat *</label><input type="number" name="hq_stock" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Status *</label>
                <select name="status" class="w-full px-3 py-2 border border-stone-300 rounded-lg"><option value="active">active</option><option value="inactive">inactive</option></select>
            </div>
            <div class="col-span-2"><label class="block text-xs font-semibold mb-1">Deskripsi</label><textarea name="description" rows="2" class="w-full px-3 py-2 border border-stone-300 rounded-lg"></textarea></div>
            <div class="col-span-2"><label class="block text-xs font-semibold mb-1">Foto Produk (jpg/png/webp, maks 2MB)</label><input type="file" name="image" accept="image/*" class="w-full text-xs"></div>
            <div class="col-span-2 flex justify-end gap-2 mt-2">
                <button type="button" onclick="toggleModal('productModal')" class="px-4 py-2 text-stone-600 rounded-lg">Batal</button>
                <button class="px-5 py-2 bg-red-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openProduct(p) {
        const f = document.getElementById('productForm');
        if (p) {
            f.action = '/products/' + p.id;
            document.getElementById('productMethod').value = 'PUT';
            document.getElementById('productModalTitle').textContent = 'Edit Produk';
            for (const k of ['name','sku','category','description','price_distributor','price_reseller','price_retail','cogs','hq_stock','status']) {
                if (f.querySelector('[name='+k+']')) f.querySelector('[name='+k+']').value = p[k] ?? '';
            }
        } else {
            f.action = '{{ route('products.store') }}';
            document.getElementById('productMethod').value = 'POST';
            document.getElementById('productModalTitle').textContent = 'Tambah Produk';
            f.reset();
        }
        toggleModal('productModal');
    }
</script>
@endpush
