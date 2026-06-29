<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Autentikasi hanya melalui tabel SQL `users`.
     * - Menerima input username ATAU email di kolom yang sama.
     * - Tidak menggunakan Firestore, auto-create, atau bypass mode developer.
     * - Akun tidak aktif / terhapus tidak dapat login.
     */
    public function login(Request $request): RedirectResponse
    {
        // Validasi input form login
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], [
            'login' => 'Username/Email',
        ]);

        $identifier = trim($data['login']);
        $genericError = ['login' => 'Username atau password salah.']; // Pesan error generik untuk alasan keamanan

        // Mencari pengguna di database.
        // Cek apakah input mengandung '@' untuk menentukan apakah itu email atau username.
        $query = User::query();
        if (str_contains($identifier, '@')) {
            $query->where('email', mb_strtolower($identifier));
        } else {
            $query->where('username', mb_strtolower($identifier));
        }

        /** @var User|null $user */
        $user = $query->first(); // Mengambil hasil pencarian pertama

        // Jika user tidak ditemukan, atau hash password tidak cocok, lempar pesan error
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages($genericError);
        }

        // Cek jika akun telah ditandai sebagai 'deleted' (dihapus sementara/permanen)
        if ($user->status === User::STATUS_DELETED) {
            throw ValidationException::withMessages([
                'login' => 'Akun ini telah dihapus dari sistem. Hubungi Super Admin.',
            ]);
        }

        // Cek jika akun dinonaktifkan (misal karena suspend oleh admin)
        if ($user->status !== User::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'login' => 'Sesi ditolak. Akun mitra SKINKU Anda dinonaktifkan oleh Administrator.',
            ]);
        }

        // Autentikasi berhasil, set sesi login pengguna
        Auth::login($user, $request->boolean('remember'));
        
        // Regenerasi sesi untuk mencegah serangan session fixation
        $request->session()->regenerate();

        // Mencatat aktivitas login di sistem audit log
        AuditService::log(
            action: 'login',
            targetType: 'user',
            targetId: $user->id,
            targetUserId: $user->id,
            targetEmail: $user->email,
        );

        // Arahkan ke halaman yang sebelumnya coba diakses, atau default ke dashboard
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Proses logout untuk pengguna yang sedang aktif.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            // Mencatat aktivitas logout di audit log sebelum sesi dihapus
            AuditService::log(
                action: 'logout',
                targetType: 'user',
                targetId: $user->id,
                targetUserId: $user->id,
                targetEmail: $user->email,
            );
        }

        // Hapus status otentikasi dari sistem
        Auth::logout();
        
        // Invalidasi sesi dan regenerasi token CSRF untuk mencegah serangan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke halaman login
        return redirect()->route('login');
    }

    /* ---------------- Forgot / reset password (email link) ---------------- */

    /**
     * Menampilkan halaman lupa password (form pengisian email)
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Mengirim link reset password ke email yang diberikan
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        // Pastikan email disediakan dalam request
        $request->validate(['email' => ['required', 'email']]);

        // Proses pengiriman mungkin gagal (contoh: SMTP error). 
        // Tangkap dengan try-catch agar halaman tidak error 500 dan menjaga kerahasiaan ada/tidaknya email.
        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e); // Laporkan error secara internal
        }

        // Berikan pesan sukses yang generik terlepas berhasil dikirim atau email tidak ditemukan
        return back()->with('status', 'Jika email terdaftar, link reset password akan dikirim ke email tersebut.');
    }

    /**
     * Menampilkan form pembuatan password baru menggunakan token dari email
     */
    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Proses reset password (mengubah password lama yang dilupakan ke yang baru)
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        // Validasi kelengkapan token, kecocokan kedua input password, dan kriteria minimum (8 karakter)
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        // Fungsi bawaan Laravel untuk memverifikasi token dan mengubah password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Perbarui password dengan hash baru dan ubah token "remember me" secara paksa
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Catat di log bahwa pengguna baru saja me-reset sandinya
                AuditService::log(
                    action: 'reset_password',
                    targetType: 'user',
                    targetId: $user->id,
                    targetUserId: $user->id,
                    targetEmail: $user->email,
                );
            }
        );

        // Jika berhasil diubah, bawa ke halaman login
        if ($status === Password::PasswordReset) {
            return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login.');
        }

        // Jika gagal (contoh: token kadaluarsa), lemparkan error ke form
        throw ValidationException::withMessages(['email' => __($status)]);
    }

    /* ---------------- Change own password (logged in) ---------------- */

    /**
     * Menampilkan halaman ubah password untuk pengguna yang sudah login (profil akun)
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Proses pengubahan password oleh pengguna yang sudah login
     */
    public function changePassword(Request $request): RedirectResponse
    {
        // Validasi: periksa sandi saat ini dan pastikan sandi baru sesuai konfirmasi dan minimal 8 karakter
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        // Simpan password hash yang baru
        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Catat kejadian ubah password di log audit
        AuditService::log(
            action: 'change_password',
            targetType: 'user',
            targetId: $user->id,
            targetUserId: $user->id,
            targetEmail: $user->email,
        );

        // Kembali dengan pesan sukses
        return back()->with('status', 'Password Anda berhasil diperbarui.');
    }
}
