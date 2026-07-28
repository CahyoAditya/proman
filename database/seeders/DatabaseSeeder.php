<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan RoleSeeder terlebih dahulu
        $this->call(RoleSeeder::class);

        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        // Buat Super Admin User dari Environment Variable jika diset
        if ($adminEmail && $adminPassword) {
            $admin = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => env('ADMIN_NAME', 'Super Admin'),
                    'password' => Hash::make($adminPassword),
                    'email_verified_at' => now(),
                ]
            );

            $admin->assignRole('super_admin');
        }
    }
}
