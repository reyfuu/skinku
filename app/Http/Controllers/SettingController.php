<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * System settings. Read-only environment summary for super_admin.
     * (Sensitive settings live in .env and are never editable from the UI.)
     */
    public function index(Request $request)
    {
        $info = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_url' => config('app.url'),
            'db_driver' => config('database.default'),
            'db_database' => config('database.connections.'.config('database.default').'.database'),
            'mail_mailer' => config('mail.default'),
            'filesystem' => config('filesystems.default'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];

        return view('settings.index', compact('info'));
    }
}
