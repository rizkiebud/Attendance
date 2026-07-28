<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'jabatan',
        'departemen',
        'foto',
        'telepon',
        'alamat',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)->whereDate('tanggal', today());
    }
}
