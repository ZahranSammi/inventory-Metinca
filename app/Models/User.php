<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'employee_id',
        'phone',
    ];

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    public function isAccounting(): bool
    {
        return in_array($this->role, ['staff_accounting', 'accounting']);
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isDirektur(): bool
    {
        return in_array($this->role, ['direktur_sdm', 'direktur']);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'staff_accounting', 'accounting' => 'Staff Accounting',
            'manager' => 'Manager',
            'direktur_sdm', 'direktur' => 'Direktur SDM',
            default => 'Karyawan',
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
