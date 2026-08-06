<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan role inti ada (admin, karyawan) sebelum memetakan.
        $ensure = function (string $name, string $label, string $level) {
            if (!DB::table('roles')->where('name', $name)->exists()) {
                DB::table('roles')->insert([
                    'name' => $name, 'label' => $label, 'level' => $level,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        };
        $ensure('admin', 'Administrator', 'full');
        $ensure('karyawan', 'Karyawan', 'view');

        $adminId = DB::table('roles')->where('name', 'admin')->value('id');
        $karyawanId = DB::table('roles')->where('name', 'karyawan')->value('id');

        // User lama: admin -> role admin, sisanya -> karyawan.
        DB::table('users')->where('role', 'admin')->whereNull('role_id')
            ->update(['role_id' => $adminId]);
        DB::table('users')->whereNull('role_id')
            ->update(['role_id' => $karyawanId, 'role' => 'karyawan']);
    }

    public function down(): void
    {
        //
    }
};