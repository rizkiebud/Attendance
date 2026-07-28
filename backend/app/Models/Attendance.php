<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'office_id',
        'tanggal',
        'jam_masuk',
        'lat_masuk',
        'lng_masuk',
        'jarak_masuk',
        'foto_masuk',
        'jam_keluar',
        'lat_keluar',
        'lng_keluar',
        'jarak_keluar',
        'foto_keluar',
        'status',
        'keterangan',
        'device_info',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'lat_masuk' => 'decimal:8',
        'lng_masuk' => 'decimal:8',
        'lat_keluar' => 'decimal:8',
        'lng_keluar' => 'decimal:8',
        'jarak_masuk' => 'decimal:2',
        'jarak_keluar' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function getDurasiKerjaAttribute(): ?string
    {
        if ($this->jam_masuk && $this->jam_keluar) {
            $masuk = \Carbon\Carbon::parse($this->jam_masuk);
            $keluar = \Carbon\Carbon::parse($this->jam_keluar);
            $diff = $masuk->diff($keluar);
            return $diff->format('%H:%I');
        }
        return null;
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'success',
            'terlambat' => 'warning',
            'tidak_hadir' => 'danger',
            'izin' => 'info',
            'sakit' => 'secondary',
            'libur' => 'dark',
            default => 'secondary',
        };
    }
}
