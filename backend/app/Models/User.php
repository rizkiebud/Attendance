<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'role_id',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || strtolower((string) $this->roleModel?->name) === 'admin';
    }

    public function accessLevel(): ?string
    {
        if ($this->isAdmin()) return 'full';

        $role = $this->roleModel;
        // Role karyawan tidak punya akses ke web dashboard
        if ($role && strtolower($role->name) === 'karyawan') return null;
        // HRD = level khusus (manage + kantor & lokasi, tanpa penggajian)
        if ($role && strtolower($role->name) === 'hrd') return 'hrd';

        return $role?->level;
    }

    public function isSupervisor(): bool
    {
        return $this->isAdmin() || strtolower((string) $this->roleModel?->name) === 'supervisor';
    }

    public function isHrd(): bool
    {
        return strtolower((string) $this->roleModel?->name) === 'hrd';
    }

    /**
     * Akses ke menu Kantor & Lokasi: admin (full) atau role HRD.
     */
    public function canManageOffices(): bool
    {
        return $this->isAdmin() || $this->isHrd() || $this->accessLevel() === 'full';
    }
}
