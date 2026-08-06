<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('uang_makan', 12, 2)->default(0)->after('tunjangan');
            $table->decimal('uang_transport', 12, 2)->default(0)->after('uang_makan');
            $table->decimal('tunjangan_jabatan', 12, 2)->default(0)->after('uang_transport');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['uang_makan', 'uang_transport', 'tunjangan_jabatan']);
        });
    }
};