@extends('layouts.app')
@section('title', 'Detail PO')
@section('heading', 'Detail Purchase Order')

@section('content')
@php $u = $user; @endphp
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-stone-900">{{ $purchaseOrder->po_number }}</h3>
                    <p class="text-xs text-stone-500 mt-1">{{ $purchaseOrder->created_at?->format('d M Y H:i') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-stone-100 text-stone-700">{{ $purchaseOrder->status }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-5 text-xs">
                <div><p class="text-stone-400 uppercase">Mitra</p><p class="font-semibold text-stone-800">{{ $purchaseOrder->company_name ?? ($purchaseOrder->user->fullname ?? '-') }}</p></div>
                <div><p class="text-stone-400 uppercase">Role</p><p class="font-semibold text-stone-800">{{ $purchaseOrder->user_role }}</p></div>
                <div class="col-span-2"><p class="text-stone-400 uppercase">Alamat Pengiriman</p><p class="text-stone-700">{{ $purchaseOrder->shipping_address ?? '-' }}</p></div>
                @if($purchaseOrder->notes)<div class="col-span-2"><p class="text-stone-400 uppercase">Catatan</p><p class="text-stone-700">{{ $purchaseOrder->notes }}</p></div>@endif
                @if($purchaseOrder->revision_notes)<div class="col-span-2"><p class="text-stone-400 uppercase">Catatan Revisi/Admin</p><p class="text-amber-700">{{ $purchaseOrder->revision_notes }}</p></div>@endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-stone-100 text-sm font-bold text-stone-800">Item PO</div>
            <table class="w-full text-xs">
                <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
                    <tr><th class="text-left px-4 py-2">Produk</th><th class="text-left">SKU</th><th class="text-right">Qty</th><th class="text-right">Harga</th><th class="text-right px-4">Subtotal</th></tr>
                </thead>
                <tbody>
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
                    <tr class="border-t-2 border-stone-200 font-bold">
                        <td colspan="4" class="px-4 py-3 text-right">Total</td>
                        <td class="px-4 py-3 text-right text-emerald-700">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        {{-- Status update for staff --}}
        @if($u->isStaff() && count($nextStatuses) > 0)
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="text-sm font-bold text-stone-800 mb-3">Perbarui Status</h3>
                <form method="POST" action="{{ route('purchase-orders.status', $purchaseOrder) }}" class="space-y-3"
                      onsubmit="return confirm('Ubah status PO?')">
                    @csrf
                    <select name="status" class="w-full px-3 py-2 text-sm border border-stone-300 rounded-lg">
                        @foreach($nextStatuses as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                    </select>
                    <textarea name="notes" rows="2" placeholder="Catatan (opsional)" class="w-full px-3 py-2 text-sm border border-stone-300 rounded-lg"></textarea>
                    <button class="w-full py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl">Perbarui Status</button>
                </form>
                @if(in_array('completed', $nextStatuses))
                    <p class="text-[10px] text-amber-600 mt-2">Menyelesaikan PO akan otomatis mengurangi stok pusat & menambah stok mitra (transaksi DB).</p>
                @endif
            </div>
        @endif

        {{-- Cancel --}}
        @if(!in_array($purchaseOrder->status, ['completed','cancelled','deleted']))
            @if($u->isManagement() || ($u->isPartner() && in_array($purchaseOrder->status, ['pending','draft'])))
                <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Batalkan PO ini?')">
                    @csrf
                    <button class="w-full py-2.5 bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold rounded-xl hover:bg-rose-100">Batalkan PO</button>
                </form>
            @endif
        @endif

        {{-- Delete (management) --}}
        @if($u->isManagement() && $purchaseOrder->status !== 'deleted')
            <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" onsubmit="return confirm('Hapus PO (soft delete)?')">
                @csrf @method('DELETE')
                <button class="w-full py-2.5 text-stone-500 text-xs font-semibold rounded-xl hover:text-rose-600">Hapus PO (soft delete)</button>
            </form>
        @endif

        <a href="{{ route('purchase-orders.index') }}" class="block text-center text-xs text-stone-500 hover:text-stone-800">← Kembali ke daftar PO</a>
    </div>
</div>
@endsection
