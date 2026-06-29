<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Menampilkan daftar Audit Log (catatan aktivitas) dengan dukungan pencarian dan filter.
     */
    public function index(Request $request)
    {
        // Mengambil hanya parameter 'action' (jenis aktivitas) dan 'q' (kata kunci pencarian) dari input request
        $filters = $request->only(['action', 'q']);

        // Mengambil data log audit dari database
        $logs = AuditLog::query()
            ->with('performer') // Memuat relasi 'performer' (user yang melakukan aksi) agar lebih efisien (eager loading)
            ->when($filters['action'] ?? null, fn ($q, $a) => $q->where('action', $a)) // Filter berdasarkan tipe 'action' jika tersedia
            ->when($filters['q'] ?? null, function ($q, $term) {
                // Filter pencarian teks ('q') menggunakan logika OR pada beberapa kolom
                $q->where(function ($sub) use ($term) {
                    $sub->where('target_email', 'like', "%{$term}%") // Cari di email target
                        ->orWhere('performed_by_email', 'like', "%{$term}%") // Cari di email pelaku
                        ->orWhere('action', 'like', "%{$term}%"); // Cari di tipe aksi
                });
            })
            ->orderByDesc('created_at') // Mengurutkan dari log terbaru
            ->paginate(30) // Membatasi hasil 30 baris per halaman
            ->withQueryString(); // Mempertahankan parameter filter pada link paginasi

        // Mengambil daftar nama aksi unik (distinct) untuk digunakan pada dropdown filter
        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        // Mengembalikan view daftar audit log dengan data logs, parameter filter, dan daftar aksi unik
        return view('audit_logs.index', compact('logs', 'filters', 'actions'));
    }
}
