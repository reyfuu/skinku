<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the initial super admin from environment variables.
 * Idempotent: does nothing if a super admin with that email/username exists.
 * Credentials are NEVER hardcoded — set them in .env.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        $username = env('SUPER_ADMIN_USERNAME');
        $password = env('SUPER_ADMIN_PASSWORD');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        if (! $email || ! $username || ! $password) {
            $this->command?->warn('SuperAdminSeeder dilewati: SUPER_ADMIN_EMAIL/USERNAME/PASSWORD belum diset di .env.');

            return;
        }

        $exists = User::where('email', mb_strtolower($email))
            ->orWhere('username', mb_strtolower($username))
            ->exists();

        if ($exists) {
            $this->command?->info('Super admin sudah ada — seeder dilewati.');

            return;
        }

        User::create([
            'name' => $name,
            'fullname' => $name,
            'email' => mb_strtolower($email),
            'username' => mb_strtolower($username),
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPER_ADMIN,
            'company_name' => 'SKINKU Indonesia (HQ)',
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->command?->info("Super admin '{$username}' berhasil dibuat.");
    }
}
