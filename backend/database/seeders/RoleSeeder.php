<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'label' => 'Administrator', 'level' => 'full'],
            ['name' => 'supervisor', 'label' => 'Supervisor', 'level' => 'manage'],
            ['name' => 'staf', 'label' => 'Staf', 'level' => 'view'],
            ['name' => 'karyawan', 'label' => 'Karyawan', 'level' => 'view'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['name' => $r['name']], $r);
        }

        // Sync existing users to role assignment by name
        $admin = Role::where('name', 'admin')->first();
        $karyawan = Role::where('name', 'karyawan')->first();

        User::where('role', 'admin')->update(['role_id' => $admin->id]);
        User::where('role', 'karyawan')->whereNull('role_id')->update(['role_id' => $karyawan->id]);
    }
}