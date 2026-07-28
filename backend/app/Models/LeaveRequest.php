<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'dokumen',
        'status',
        'catatan_admin',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDurasiAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }
}
