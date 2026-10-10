<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'loyalty_visits',
    ];

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function groomingAppointments()
    {
        return $this->hasMany(GroomingAppointment::class);
    }

    public function groomingVisitGroups()
    {
        return $this->hasMany(GroomingVisitGroup::class);
    }

    public function loyaltyVouchers()
    {
        return $this->hasMany(LoyaltyVoucher::class);
    }

    public function activeVoucher()
    {
        return $this->hasOne(LoyaltyVoucher::class)
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now()->toDateString());
    }
    public function vouchers()
{
    return $this->hasMany(Voucher::class);
}
}