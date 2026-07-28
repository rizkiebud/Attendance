<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->decimal('lat_masuk', 10, 8)->nullable();
            $table->decimal('lng_masuk', 11, 8)->nullable();
            $table->decimal('jarak_masuk', 10, 2)->nullable(); // dalam meter
            $table->string('foto_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->decimal('lat_keluar', 10, 8)->nullable();
            $table->decimal('lng_keluar', 11, 8)->nullable();
            $table->decimal('jarak_keluar', 10, 2)->nullable();
            $table->string('foto_keluar')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'tidak_hadir', 'izin', 'sakit', 'libur'])->default('tidak_hadir');
            $table->text('keterangan')->nullable();
            $table->string('device_info')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
