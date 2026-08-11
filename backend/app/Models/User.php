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
        return $this->role === 'admin' || $this->roleModel?->name === 'admin';
    }

    public function accessLevel(): ?string
    {
        if ($this->isAdmin()) return 'full';

        $role = $this->roleModel;
        // Role karyawan tidak punya akses ke web dashboard
        if ($role && $role->name === 'karyawan') return null;

        return $role?->level;
    }

    public function isSupervisor(): bool
    {
        return $this->isAdmin() || $this->roleModel?->name === 'supervisor';
    }
}
