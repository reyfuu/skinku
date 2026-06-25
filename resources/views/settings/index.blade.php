@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('heading', 'Pengaturan Sistem')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <h3 class="text-sm font-bold text-stone-900 mb-1">Ringkasan Lingkungan</h3>
        <p class="text-xs text-stone-500 mb-5">Konfigurasi sensitif dikelola melalui file <code class="bg-stone-100 px-1 rounded">.env</code> dan tidak dapat diubah dari UI demi keamanan.</p>
        <dl class="divide-y divide-stone-100 text-sm">
            @foreach($info as $key => $value)
                <div class="flex justify-between py-2.5">
                    <dt class="text-stone-500 uppercase text-xs tracking-wide">{{ str_replace('_', ' ', $key) }}</dt>
                    <dd class="font-semibold text-stone-800">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 p-6 mt-6">
        <h3 class="text-sm font-bold text-stone-900 mb-2">Catatan Keamanan</h3>
        <ul class="text-xs text-stone-600 space-y-1.5 list-disc list-inside">
            <li>Sumber kebenaran user adalah tabel SQL <code class="bg-stone-100 px-1 rounded">users</code> — tidak ada Firestore.</li>
            <li>Password disimpan ter-hash (bcrypt). Tidak ada plaintext.</li>
            <li>Semua aksi sensitif tercatat di Audit Log.</li>
            <li>Soft delete diterapkan pada user, produk, dan PO untuk menjaga histori.</li>
            <li>Untuk beralih ke PostgreSQL, ubah <code class="bg-stone-100 px-1 rounded">DB_CONNECTION=pgsql</code> di .env.</li>
        </ul>
    </div>
</div>
@endsection
