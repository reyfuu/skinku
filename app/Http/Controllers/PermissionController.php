<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return view('permissions.index', [
            'definitions' => Permissions::DEFINITIONS,
            'roles' => Permissions::ROLES,
            'matrix' => Permissions::matrix(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        Permissions::save($request->input('perm', []));

        AuditService::log(
            action: 'update_permissions',
            targetType: 'system',
            after: ['updated' => true],
        );

        return back()->with('status', 'Hak akses tiap role berhasil diperbarui.');
    }
}
