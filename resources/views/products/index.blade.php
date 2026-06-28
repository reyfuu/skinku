@extends('layouts.app')
@section('title', 'Manajemen Produk')
@section('heading', 'Manajemen Produk')

@section('content')
<div class="flex justify-between items-center mb-4">
    <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama/SKU…" class="px-3 py-2 text-sm border border-stone-300 rounded-lg w-56" onkeydown="if(event.key === 'Enter'){ this.form.submit(); }">
        <select name="status" class="px-3 py-2 text-sm border border-stone-300 rounded-lg" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach(['active','inactive','deleted'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '')===$s)>{{ $s }}</option>@endforeach
        </select>
    </form>
    <button onclick="openProduct()" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">+ Tambah Produk</button>
</div>

<div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
            <tr>
                <th class="text-left px-4 py-3 whitespace-nowrap">Produk</th>
                <th class="text-left px-4 py-3 whitespace-nowrap">SKU</th>
                <th class="text-left px-4 py-3 whitespace-nowrap">Kategori</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Distributor</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Reseller</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Retail</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">HPP</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Stok Pusat</th>
                <th class="text-center px-4 py-3 whitespace-nowrap">Status</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $p)
                <tr class="border-t border-stone-100 hover:bg-stone-50">
                    @php $urls = $p->imageUrls(); @endphp
                    <td class="px-4 py-3 font-semibold text-stone-800">
                        <div class="flex items-center gap-2">
                            @if(count($urls))
                                <a href="{{ $urls[0] }}" class="glightbox shrink-0" data-gallery="prod-{{ $p->id }}" title="Klik untuk lihat foto">
                                    <img src="{{ $urls[0] }}" class="w-10 h-10 rounded object-cover border border-stone-200 hover:opacity-80 transition">
                                </a>
                                @foreach(array_slice($urls, 1) as $u)
                                    <a href="{{ $u }}" class="glightbox" data-gallery="prod-{{ $p->id }}" style="display:none"></a>
                                @endforeach
                            @else
                                <span class="w-10 h-10 rounded bg-stone-100 flex items-center justify-center shrink-0">{{ $p->image ?: '🧴' }}</span>
                            @endif
                            <div class="flex flex-col">
                                <span>{{ $p->name }}</span>
                                @if(count($urls) > 1)<span class="text-[9px] text-stone-400">({{ count($urls) }} foto)</span>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-stone-600">{{ $p->sku }}</td>
                    <td class="px-4 py-3 text-stone-600">{{ $p->category ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($p->price_distributor, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($p->price_reseller, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($p->price_retail, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-stone-500">Rp {{ number_format($p->cogs, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-bold {{ $p->hq_stock <= 0 ? 'text-rose-600' : 'text-stone-800' }}">{{ $p->hq_stock }}</td>
                    <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-[10px] {{ $p->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600' }}">{{ $p->status }}</span></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($p->status !== 'deleted')
                            <div class="flex items-center justify-end gap-1.5">
                                @php $gallery = $p->fileGallery(\App\Models\Product::GALLERY); @endphp
                                <button title="Edit Produk" class="flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200 hover:text-stone-900 transition-colors"
                                    onclick='openProduct({{ json_encode($p->only(["id","name","sku","category","description","price_distributor","price_reseller","price_retail","cogs","hq_stock","status"]) + ["gallery" => $gallery]) }})'>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    <span>Edit</span>
                                </button>
                                <form method="POST" action="{{ route('products.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus produk ini (soft delete)?')">
                                    @csrf @method('DELETE')
                                    <button title="Hapus Produk" class="flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 hover:text-rose-900 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </div>
                        @else 
                            <span class="text-stone-400">—</span> 
                        @endif
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
<div id="productModal" class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto no-scrollbar">
        <div class="flex justify-between items-center mb-4">
            <h3 id="productModalTitle" class="text-sm font-bold text-stone-900">Tambah Produk</h3>
            <button onclick="toggleModal('productModal')" class="text-stone-400 hover:text-stone-700">✕</button>
        </div>
        <form method="POST" id="productForm" enctype="multipart/form-data" action="{{ old('product_id') ? route('products.update', old('product_id')) : route('products.store') }}" class="grid grid-cols-2 gap-3 text-sm">
            @csrf
            <input type="hidden" name="_method" id="productMethod" value="{{ old('_method', 'POST') }}">
            <input type="hidden" name="product_id" id="productIdInput" value="{{ old('product_id') }}">

            @if($errors->any())
                <div class="col-span-2 mb-2 px-4 py-3 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs">
                    <span class="font-bold">Gagal menyimpan produk:</span>
                    <ul class="list-disc ml-5 mt-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="col-span-2"><label class="block text-xs font-semibold mb-1">Nama *</label><input name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">SKU *</label><input name="sku" value="{{ old('sku') }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div>
                <label class="block text-xs font-semibold mb-1">Kategori</label>
                <input list="category-options" name="category" value="{{ old('category') }}" placeholder="Pilih atau ketik baru..." class="w-full px-3 py-2 border border-stone-300 rounded-lg">
                <datalist id="category-options">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div><label class="block text-xs font-semibold mb-1">Harga Distributor *</label><input type="number" step="0.01" name="price_distributor" value="{{ old('price_distributor') }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Harga Reseller *</label><input type="number" step="0.01" name="price_reseller" value="{{ old('price_reseller') }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Harga Retail *</label><input type="number" step="0.01" name="price_retail" value="{{ old('price_retail') }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">HPP / COGS *</label><input type="number" step="0.01" name="cogs" value="{{ old('cogs') }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Stok Pusat *</label><input type="number" name="hq_stock" value="{{ old('hq_stock') }}" required class="w-full px-3 py-2 border border-stone-300 rounded-lg"></div>
            <div><label class="block text-xs font-semibold mb-1">Status *</label>
                <select name="status" class="w-full px-3 py-2 border border-stone-300 rounded-lg"><option value="active" @selected(old('status') === 'active')>active</option><option value="inactive" @selected(old('status') === 'inactive')>inactive</option></select>
            </div>
            <div class="col-span-2"><label class="block text-xs font-semibold mb-1">Deskripsi</label><textarea name="description" rows="2" class="w-full px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-y">{{ old('description') }}</textarea></div>
            <div class="col-span-2">
                <label class="block text-xs font-semibold mb-1">Foto Produk (maks 8 foto · otomatis di-resize)</label>
                <div id="productExistingImages" class="flex flex-wrap gap-2 mb-2"></div>
                <div class="flex items-center gap-3">
                    <label class="cursor-pointer px-4 py-2 bg-red-100 text-red-700 hover:bg-red-200 text-xs font-bold rounded-lg transition-colors border border-red-200">
                        <span>Pilih Foto...</span>
                        <input type="file" id="productImagesInput" name="images[]" accept="image/*" multiple class="hidden" onchange="document.getElementById('file-chosen-text').textContent = this.files.length > 0 ? this.files.length + ' file dipilih' : 'Tidak ada file dipilih'">
                    </label>
                    <span id="file-chosen-text" class="text-xs text-stone-500 font-medium">Tidak ada file dipilih</span>
                </div>
                <p class="text-[10px] text-stone-400 mt-2">Bisa pilih beberapa foto sekaligus. Foto besar otomatis dikecilkan agar hemat penyimpanan. Saat edit, centang "hapus" untuk membuang foto lama.</p>
            </div>
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
        f.reset(); // Kosongkan file input dan isian lama agar upload foto di Edit berfungsi normal
        document.getElementById('file-chosen-text').textContent = 'Tidak ada file dipilih';
        const existing = document.getElementById('productExistingImages');
        existing.innerHTML = '';
        if (p) {
            f.action = '/products/' + p.id;
            document.getElementById('productMethod').value = 'PUT';
            document.getElementById('productIdInput').value = p.id;
            document.getElementById('productModalTitle').textContent = 'Edit Produk';
            for (const k of ['name','sku','category','description','price_distributor','price_reseller','price_retail','cogs','hq_stock','status']) {
                if (f.querySelector('[name='+k+']')) f.querySelector('[name='+k+']').value = p[k] ?? '';
            }
            // render existing gallery with "hapus" checkboxes
            (p.gallery || []).forEach(img => {
                const wrap = document.createElement('label');
                wrap.className = 'relative block w-16 h-16 cursor-pointer';
                wrap.innerHTML =
                    '<img src="' + img.url + '" class="w-16 h-16 object-cover rounded-lg border border-stone-200">' +
                    '<span class="absolute -top-1 -right-1 bg-white rounded-full border border-stone-200 p-0.5">' +
                    '<input type="checkbox" name="remove_files[]" value="' + img.id + '" class="accent-red-600" title="hapus"></span>';
                existing.appendChild(wrap);
            });
        } else {
            f.action = '{{ route('products.store') }}';
            document.getElementById('productMethod').value = 'POST';
            document.getElementById('productIdInput').value = '';
            document.getElementById('productModalTitle').textContent = 'Tambah Produk';
            f.reset();
        }
        toggleModal('productModal');
    }
</script>
@endpush
