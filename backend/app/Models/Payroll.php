<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'periode',
        'gaji_pokok',
        'tunjangan',
        'uang_makan',
        'uang_transport',
        'tunjangan_jabatan',
        'jumlah_hadir',
        'bonus',
        'potongan',
        'status',
        'tanggal_bayar',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'periode' => 'date',
        'tanggal_bayar' => 'date',
        'gaji_pokok' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'uang_makan' => 'decimal:2',
        'uang_transport' => 'decimal:2',
        'tunjangan_jabatan' => 'decimal:2',
        'jumlah_hadir' => 'integer',
        'bonus' => 'decimal:2',
        'potongan' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalAttribute(): float
    {
        $tunjanganHarian = $this->tunjangan
            + $this->uang_makan
            + $this->uang_transport
            + $this->tunjangan_jabatan;

        return round(
            $this->gaji_pokok
                + $tunjanganHarian * $this->jumlah_hadir
                + $this->bonus
                - $this->potongan,
            2
        );
    }

    /** Hitung jumlah hari masuk (hadir + terlambat) dalam periode. */
    public static function hitungJumlahHadir(int $employeeId, string $periode): int
    {
        $awal = \Carbon\Carbon::parse($periode)->startOfMonth();
        $akhir = \Carbon\Carbon::parse($periode)->endOfMonth();

        return Attendance::where('employee_id', $employeeId)
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();
    }

    /**
     * Hitung potongan otomatis dari absensi sebulan.
     * harian = gaji_pokok / jumlah hari dalam bulan periode.
     * potongan = (tidak_hadir + izin + sakit) x harian + terlambat x 0.5 x harian.
     */
    public static function hitungPotonganDariAbsensi(int $employeeId, string $periode, float $gajiPokok): float
    {
        $awal = \Carbon\Carbon::parse($periode)->startOfMonth();
        $akhir = \Carbon\Carbon::parse($periode)->endOfMonth();
        $jumlahHari = $awal->daysInMonth;

        $counts = Attendance::where('employee_id', $employeeId)
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->selectRaw("SUM(status = 'tidak_hadir') as tidak_hadir")
            ->selectRaw("SUM(status = 'izin') as izin")
            ->selectRaw("SUM(status = 'sakit') as sakit")
            ->selectRaw("SUM(status = 'terlambat') as terlambat")
            ->first();

        $harian = $jumlahHari > 0 ? $gajiPokok / $jumlahHari : 0;

        $tidakHadir = (int) ($counts->tidak_hadir ?? 0);
        $izin = (int) ($counts->izin ?? 0);
        $sakit = (int) ($counts->sakit ?? 0);
        $terlambat = (int) ($counts->terlambat ?? 0);

        return round(
            ($tidakHadir + $izin + $sakit) * $harian
                + $terlambat * 0.5 * $harian,
            2
        );
    }
}