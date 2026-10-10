<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function groomingAppointments()
    {
        return $this->hasMany(
            GroomingAppointment::class,
            'groomer_id'
        );
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isFrontDesk(): bool
    {
        return $this->role === 'frontdesk';
    }

    public function isGroomer(): bool
    {
        return $this->role === 'groomer';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function landingRoute(): string
    {
        return match ($this->role) {
            'owner' => 'dashboard',
            'frontdesk' => 'dashboard',
            'customer' => 'pos.index',
            default => 'dashboard',
        };
    }

    public function canOperate(): bool
    {
        return in_array($this->role, [
            'admin',
            'manager',
            'frontdesk',
        ]);
    }

    public function canManageInventory(): bool
    {
        return in_array($this->role, [
            'admin',
            'manager',
        ]);
    }

    public function canAccessCustomers(): bool
    {
        return in_array($this->role, [
            'admin',
            'manager',
            'frontdesk',
        ]);
    }

    public function canAllocateStock(): bool
    {
        return in_array($this->role, [
            'admin',
            'manager',
            'frontdesk',
        ]);
    }

    public function canGroom(): bool
    {
        return in_array($this->role, [
            'admin',
            'manager',
            'groomer',
        ]);
    }
}