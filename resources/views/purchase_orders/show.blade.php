@extends('layouts.app')
@section('title', 'Detail PO')
@section('heading', 'Detail Purchase Order')

@section('content')
@php
    // Inisialisasi variabel user yang sedang login dan data purchase order
    $u = $user;
    $po = $purchaseOrder;

    // Menentukan badge status pembayaran berdasarkan payment_status dari PO
    $payBadge = [
        'unpaid'                => ['Belum Dibayar', 'bg-stone-100 text-stone-600'],
        'awaiting_verification' => ['Menunggu Verifikasi', 'bg-amber-100 text-amber-700'],
        'paid'                  => ['Lunas', 'bg-emerald-100 text-emerald-700'],
        'rejected'              => ['Bukti Ditolak', 'bg-rose-100 text-rose-700'],
    ][$po->payment_status] ?? ['-', 'bg-stone-100 text-stone-600'];

    // Menentukan badge status order (PO) menggunakan match expression
    $statusBadge = match($po->status) {
        'draft'      => ['📝 Draft', 'bg-stone-100 text-stone-600'],
        'pending'    => ['⏳ Menunggu', 'bg-amber-100 text-amber-700'],
        'approved'   => ['✅ Disetujui', 'bg-blue-100 text-blue-700'],
        'processing' => ['⚙️ Diproses', 'bg-violet-100 text-violet-700'],
        'shipped'    => ['🚚 Dikirim', 'bg-cyan-100 text-cyan-700'],
        'completed'  => ['🎉 Selesai', 'bg-emerald-100 text-emerald-700'],
        'cancelled'  => ['❌ Dibatalkan', 'bg-rose-100 text-rose-600'],
        default      => [$po->status, 'bg-stone-100 text-stone-500'],
    };

    // Mengecek apakah user yang login adalah pemilik PO ini
    $isOwner = $po->user_id === $u->id;
    // Mengecek apakah user bisa mengunggah bukti pembayaran (pemilik PO atau user yang punya akses update_po_status)
    $canUploadProof = $isOwner || $u->canDo('update_po_status');
@endphp

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Kolom utama (sebelah kiri pada desktop) --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Card Informasi Umum PO --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <div class="flex justify-between items-start">
                <div>
                    {{-- Menampilkan Nomor PO dan Tanggal Pembuatan --}}
                    <h3 class="text-lg font-bold text-stone-900">{{ $purchaseOrder->po_number }}</h3>
                    <p class="text-xs text-stone-500 mt-1">{{ $purchaseOrder->created_at?->format('d M Y H:i') }}</p>
                </div>
                <div class="flex flex-col items-end gap-1.5">
                    {{-- Menampilkan Badge Status PO dan Status Pembayaran --}}
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $payBadge[1] }}">{{ $payBadge[0] }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-5 text-xs">
                {{-- Detail Mitra, Role, dan Alamat --}}
                <div><p class="text-stone-400 uppercase">Mitra</p><p class="font-semibold text-stone-800">{{ $purchaseOrder->company_name ?? ($purchaseOrder->user->fullname ?? '-') }}</p></div>
                <div><p class="text-stone-400 uppercase">Role</p><p class="font-semibold text-stone-800">{{ $purchaseOrder->user_role }}</p></div>
                <div class="col-span-2"><p class="text-stone-400 uppercase">Alamat Pengiriman</p><p class="text-stone-700">{{ $purchaseOrder->shipping_address ?? '-' }}</p></div>
                
                {{-- Menampilkan Catatan jika ada --}}
                @if($purchaseOrder->notes)
                    <div class="col-span-2"><p class="text-stone-400 uppercase">Catatan</p><p class="text-stone-700">{{ $purchaseOrder->notes }}</p></div>
                @endif
                
                {{-- Menampilkan Catatan Revisi/Admin jika ada --}}
                @if($purchaseOrder->revision_notes)
                    <div class="col-span-2"><p class="text-stone-400 uppercase">Catatan Revisi/Admin</p><p class="text-amber-700">{{ $purchaseOrder->revision_notes }}</p></div>
                @endif
            </div>
        </div>

        {{-- Card Daftar Item PO --}}
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-stone-100 text-sm font-bold text-stone-800">Item PO</div>
            <table class="w-full text-xs">
                <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
                    <tr>
                        <th class="text-left px-4 py-2">Produk</th>
                        <th class="text-left">SKU</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right px-4">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Looping melalui setiap item dalam PO --}}
                    @foreach($purchaseOrder->items as $item)
                        <tr class="border-t border-stone-100">
                            <td class="px-4 py-2 font-semibold text-stone-800">{{ $item->product_name }}</td>
                            <td class="text-stone-500">{{ $item->sku }}</td>
                            <td class="text-right">{{ $item->qty }}</td>
                            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right px-4">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    {{-- Subtotal Barang --}}
                    <tr class="border-t border-stone-100">
                        <td colspan="4" class="px-4 py-1.5 text-right text-stone-500">Subtotal Barang</td>
                        <td class="px-4 py-1.5 text-right text-stone-700">Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    
                    {{-- Tampilkan baris Diskon jika ada diskon --}}
                    @if($purchaseOrder->discount > 0)
                    <tr>
                        <td colspan="4" class="px-4 py-1.5 text-right text-stone-500">Diskon</td>
                        <td class="px-4 py-1.5 text-right text-rose-600">- Rp {{ number_format($purchaseOrder->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    
                    {{-- Ongkir --}}
                    <tr>
                        <td colspan="4" class="px-4 py-1.5 text-right text-stone-500">Ongkir
                            {{-- Jika ongkir belum diatur admin (0), beri keterangan --}}
                            @if($purchaseOrder->shipping_cost == 0)
                                <span class="text-[10px] text-amber-600">(menunggu admin)</span>
                            @endif
                        </td>
                        <td class="px-4 py-1.5 text-right text-stone-700">Rp {{ number_format($purchaseOrder->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    
                    {{-- Total Bayar Akhir --}}
                    <tr class="border-t-2 border-stone-200 font-bold">
                        <td colspan="4" class="px-4 py-3 text-right">Total Bayar</td>
                        <td class="px-4 py-3 text-right text-emerald-700">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Kolom Sidebar (sebelah kanan pada desktop) --}}
    <div class="space-y-4">
        {{-- Form Edit Ongkir & Diskon (Hanya muncul untuk Admin jika PO belum selesai/batal/dihapus) --}}
        @if($u->canDo('update_po_status') && !in_array($po->status, ['completed','cancelled','deleted']))
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="text-sm font-bold text-stone-800 mb-3">Ongkir & Diskon</h3>
                <form method="POST" action="{{ route('purchase-orders.shipping', $po) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Ongkir (Rp)</label>
                        <input type="number" name="shipping_cost" min="0" step="100" value="{{ (int) $po->shipping_cost }}"
                               class="w-full px-3 py-2 text-sm border border-stone-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-700 mb-1">Diskon (Rp, opsional)</label>
                        <input type="number" name="discount" min="0" step="100" value="{{ (int) $po->discount }}"
                               class="w-full px-3 py-2 text-sm border border-stone-300 rounded-lg">
                    </div>
                    <button class="w-full py-2.5 bg-stone-800 hover:bg-stone-900 text-white text-sm font-semibold rounded-xl">Simpan Ongkir</button>
                </form>
                <p class="text-[10px] text-stone-400 mt-2">Total otomatis dihitung ulang: subtotal − diskon + ongkir.</p>
            </div>
        @endif

        {{-- Section Pembayaran --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <h3 class="text-sm font-bold text-stone-800 mb-3">Pembayaran</h3>

            <div class="flex justify-between items-center mb-3">
                <span class="text-xs text-stone-500">Status</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $payBadge[1] }}">{{ $payBadge[0] }}</span>
            </div>

            {{-- Menampilkan bukti pembayaran jika sudah ada --}}
            @if($po->paymentProofUrl())
                <a href="{{ $po->paymentProofUrl() }}" target="_blank" class="block mb-3">
                    <img src="{{ $po->paymentProofUrl() }}" class="w-full rounded-lg border border-stone-200" alt="Bukti transfer">
                    <span class="text-[10px] text-stone-400">Klik untuk perbesar</span>
                </a>
            @endif
            
            {{-- Menampilkan catatan pembayaran jika ada --}}
            @if($po->payment_note)
                <p class="text-[11px] text-stone-500 mb-3">Catatan: {{ $po->payment_note }}</p>
            @endif

            {{-- Form Upload Bukti Pembayaran (Bisa dilihat oleh buyer atau admin, jika status pembayaran unpaid/rejected) --}}
            @if($canUploadProof && in_array($po->payment_status, ['unpaid','rejected']))
                {{-- Jika ongkir belum di set, tahan upload bukti bayar --}}
                @if($po->shipping_cost == 0)
                    <p class="text-[11px] text-amber-600">Menunggu admin menetapkan ongkir. Setelah total final muncul, Anda bisa transfer & unggah bukti.</p>
                @else
                    {{-- Form upload bukti pembayaran --}}
                    <form method="POST" action="{{ route('purchase-orders.payment-proof', $po) }}" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <p class="text-[11px] text-stone-600">Total yang harus dibayar: <strong class="text-emerald-700">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</strong></p>
                        <input type="file" name="proof" accept="image/*" required class="w-full text-xs">
                        <input type="text" name="note" placeholder="Catatan (opsional)" class="w-full px-3 py-2 text-xs border border-stone-300 rounded-lg">
                        <button class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl">Unggah Bukti Transfer</button>
                    </form>
                @endif
            @endif

            {{-- Aksi Verifikasi Bukti Pembayaran oleh Admin --}}
            @if($u->canDo('update_po_status') && $po->payment_status === 'awaiting_verification')
                <div class="space-y-2 border-t border-stone-100 pt-3 mt-1">
                    {{-- Form untuk menyetujui (Approve) pembayaran --}}
                    <form method="POST" action="{{ route('purchase-orders.verify-payment', $po) }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="decision" value="approve">
                        <button class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl" onclick="return confirm('Tandai pembayaran LUNAS?')">✓ Verifikasi Lunas</button>
                    </form>
                    
                    {{-- Form untuk menolak (Reject) pembayaran dengan alasan --}}
                    <form method="POST" action="{{ route('purchase-orders.verify-payment', $po) }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="decision" value="reject">
                        <input type="text" name="note" placeholder="Alasan ditolak (opsional)" class="w-full px-3 py-2 text-xs border border-stone-300 rounded-lg">
                        <button class="w-full py-2 border border-rose-200 text-rose-700 text-xs font-semibold rounded-xl hover:bg-rose-50">Tolak Bukti</button>
                    </form>
                </div>
            @endif

            {{-- Jika sudah lunas, tampilkan tanggal lunas --}}
            @if($po->isPaid())
                <p class="text-[11px] text-emerald-600 mt-2">Lunas pada {{ $po->paid_at?->format('d M Y H:i') }}.</p>
            @endif
        </div>

        {{-- Section Update Status PO (Hanya untuk Admin jika ada status berikutnya) --}}
        @if($u->canDo('update_po_status') && count($nextStatuses) > 0)
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="text-sm font-bold text-stone-800 mb-3">Perbarui Status</h3>
                <form method="POST" action="{{ route('purchase-orders.status', $purchaseOrder) }}" class="space-y-3"
                      onsubmit="return confirm('Ubah status PO?')">
                    @csrf
                    {{-- Dropdown pilihan status selanjutnya yang tersedia --}}
                    <select name="status" class="w-full px-3 py-2 text-sm border border-stone-300 rounded-lg">
                        @foreach($nextStatuses as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                    </select>
                    {{-- Input catatan opsional --}}
                    <textarea name="notes" rows="2" placeholder="Catatan (opsional)" class="w-full px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-y"></textarea>
                    <button class="w-full py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl">Perbarui Status</button>
                </form>
                
                {{-- Info jika statusnya bisa diset completed --}}
                @if(in_array('completed', $nextStatuses))
                    <p class="text-[10px] text-amber-600 mt-2">Menyelesaikan PO akan otomatis mengurangi stok pusat & menambah stok mitra (transaksi DB).</p>
                @endif
                
                {{-- Peringatan jika PO belum dibayar (status processing dll mungkin tertahan) --}}
                @unless($po->isPaid())
                    <p class="text-[10px] text-rose-600 mt-2">⚠ PO belum lunas — status processing/shipped/completed terkunci sampai pembayaran diverifikasi.</p>
                @endunless
            </div>
        @endif

        {{-- Tombol Batalkan PO (Untuk Admin, atau Mitra jika statusnya masih pending/draft) --}}
        @if(!in_array($purchaseOrder->status, ['completed','cancelled','deleted']))
            @if($u->canDo('update_po_status') || ($u->isPartner() && in_array($purchaseOrder->status, ['pending','draft'])))
                <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Batalkan PO ini?')">
                    @csrf
                    <button class="w-full py-2.5 bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold rounded-xl hover:bg-rose-100">Batalkan PO</button>
                </form>
            @endif
        @endif

        {{-- Tombol Hapus PO (Soft Delete) (Hanya untuk yang memiliki hak delete_po dan status PO bukan deleted) --}}
        @if($u->canDo('delete_po') && $purchaseOrder->status !== 'deleted')
            <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" onsubmit="return confirm('Hapus PO (soft delete)?')">
                @csrf @method('DELETE')
                <button class="w-full py-2.5 text-stone-500 text-xs font-semibold rounded-xl hover:text-rose-600">Hapus PO (soft delete)</button>
            </form>
        @endif

        {{-- Link kembali ke halaman Index PO --}}
        <a href="{{ route('purchase-orders.index') }}" class="block text-center text-xs text-stone-500 hover:text-stone-800">← Kembali ke daftar PO</a>
    </div>
</div>
@endsection
