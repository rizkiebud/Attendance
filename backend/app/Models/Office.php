<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'radius',
        'jam_masuk',
        'jam_keluar',
        'toleransi_terlambat',
        'aktif',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'integer',
        'aktif' => 'boolean',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Hitung jarak antara dua koordinat menggunakan formula Haversine
     * Return jarak dalam meter
     */
    public function hitungJarak(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // meter

        $latDiff = deg2rad($lat - $this->latitude);
        $lngDiff = deg2rad($lng - $this->longitude);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) *
            sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function dalamRadius(float $lat, float $lng): bool
    {
        return $this->hitungJarak($lat, $lng) <= $this->radius;
    }
}
