<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user pertama (pastikan sudah ada user di tabel users)
        $user = User::first();

        if (!$user) {
            $this->command->error('Tidak ada user di tabel users. Silakan buat user dulu dengan php artisan make:filament-user');
            return;
        }

        // Buat role super_admin jika belum ada
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // Ambil semua permission yang sudah digenerate Shield
        $permissions = Permission::all();

        if ($permissions->isEmpty()) {
            $this->command->warn('Belum ada permission. Jalankan: php artisan shield:generate --all');
        } else {
            // Assign semua permission ke role
            $role->syncPermissions($permissions);
        }

        // Assign role ke user pertama
        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

        $this->command->info("User {$user->email} sekarang jadi Super Admin ✅");
    }
}
