@extends('layouts.app')
@section('title', 'Manajemen Hak Akses')
@section('heading', 'Manajemen Hak Akses')

@section('content')
<form method="POST" action="{{ route('permissions.update') }}">
    @csrf
    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-stone-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-stone-800">Hak Akses per Role</h3>
                <p class="text-[11px] text-stone-400 mt-0.5">Centang = role boleh melakukan. Berlaku langsung ke menu &amp; akses setelah disimpan.</p>
            </div>
            <button class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">Simpan</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-stone-50 text-stone-500 uppercase text-[10px]">
                    <tr>
                        <th class="text-left px-5 py-3 w-72">Hak Akses</th>
                        @foreach($roles as $role)
                            <th class="text-center px-3 py-3">{{ str_replace('_', ' ', $role) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($definitions as $key => $label)
                        <tr class="border-t border-stone-100 hover:bg-stone-50">
                            <td class="px-5 py-3 font-semibold text-stone-800">{{ $label }}
                                <span class="block text-[10px] font-normal text-stone-400">{{ $key }}</span>
                            </td>
                            @foreach($roles as $role)
                                @php $isSuper = $role === \App\Models\User::ROLE_SUPER_ADMIN; @endphp
                                <td class="text-center px-3 py-3">
                                    <input type="checkbox"
                                           name="perm[{{ $role }}][{{ $key }}]"
                                           value="on"
                                           class="w-4 h-4 accent-red-600 align-middle"
                                           @checked($matrix[$key][$role])
                                           @disabled($isSuper)>
                                    @if($isSuper)<span class="hidden">locked</span>@endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-stone-100 flex items-center justify-between">
            <p class="text-[11px] text-stone-400">Kolom <strong>super_admin</strong> terkunci penuh — selalu punya semua akses agar Anda tidak terkunci dari sistem.</p>
            <button class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">Simpan Perubahan</button>
        </div>
    </div>
</form>
@endsection
