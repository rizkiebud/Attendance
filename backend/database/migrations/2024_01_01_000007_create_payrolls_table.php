<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            // Periode gaji, simpan tanggal pertama bulan (contoh: 2026-08-01)
            $table->date('periode');
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('tunjangan', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('potongan', 12, 2)->default(0);
            $table->enum('status', ['draft', 'lunas'])->default('draft');
            $table->date('tanggal_bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu karyawan satu gaji per periode
            $table->unique(['employee_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};