<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'code',
        'discount_percent',
        'required_visits',
        'issued_date',
        'expires_at',
        'status',
        'used_sale_id',
        'used_at',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'issued_date' => 'date',
        'expires_at' => 'date',
        'used_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function usedSale()
    {
        return $this->belongsTo(Sale::class, 'used_sale_id');
    }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && $this->expires_at
            && !now()->startOfDay()->gt($this->expires_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at
            && now()->startOfDay()->gt($this->expires_at);
    }
}