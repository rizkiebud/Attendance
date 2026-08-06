<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@kppn.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'role_id' => \App\Models\Role::where('name', 'admin')->value('id'),
        ]);

        // Sample employees
        $employees = [
            ['name' => 'Budi Santoso', 'email' => 'budi@kppn.go.id', 'nip' => '198501012010011001', 'jabatan' => 'Kepala Seksi', 'departemen' => 'Pencairan Dana'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@kppn.go.id', 'nip' => '199003152015022002', 'jabatan' => 'Staf', 'departemen' => 'Pencairan Dana'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@kppn.go.id', 'nip' => '198812202012011003', 'jabatan' => 'Kepala Seksi', 'departemen' => 'Akuntansi'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@kppn.go.id', 'nip' => '199507102018022004', 'jabatan' => 'Staf', 'departemen' => 'Akuntansi'],
            ['name' => 'Riko Pratama', 'email' => 'riko@kppn.go.id', 'nip' => '199201052016011005', 'jabatan' => 'Staf', 'departemen' => 'Umum'],
        ];

        foreach ($employees as $emp) {
            // Akses web berdasarkan role master, dipetakan dari jabatan untuk data awal
            $roleName = match ($emp['jabatan']) {
                'Kepala Seksi' => 'supervisor',
                'Staf' => 'staf',
                default => 'karyawan',
            };
            $role = \App\Models\Role::where('name', $roleName)->first();

            $user = User::create([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'password' => Hash::make('password123'),
                // Kolom `role` dibatasi enum ['admin','karyawan']; akses web via `role_id`.
                'role' => 'karyawan',
                'role_id' => $role->id,
            ]);

            Employee::create([
                'user_id' => $user->id,
                'nip' => $emp['nip'],
                'nama' => $emp['name'],
                'jabatan' => $emp['jabatan'],
                'departemen' => $emp['departemen'],
                'aktif' => true,
            ]);
        }

        // Kantor KPPN
        Office::create([
            'nama' => 'KPPN Utama',
            'alamat' => 'Jl. Lapangan Banteng Timur No. 2, Jakarta Pusat',
            'latitude' => -6.1751,
            'longitude' => 106.8272,
            'radius' => 100,
            'jam_masuk' => '08:00:00',
            'jam_keluar' => '17:00:00',
            'toleransi_terlambat' => '00:30:00',
            'aktif' => true,
        ]);
    }
}
