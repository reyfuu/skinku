<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['action', 'q']);

        $logs = AuditLog::query()
            ->with('performer')
            ->when($filters['action'] ?? null, fn ($q, $a) => $q->where('action', $a))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('target_email', 'like', "%{$term}%")
                        ->orWhere('performed_by_email', 'like', "%{$term}%")
                        ->orWhere('action', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('audit_logs.index', compact('logs', 'filters', 'actions'));
    }
}
