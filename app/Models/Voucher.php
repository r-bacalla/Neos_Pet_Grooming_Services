<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
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

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'discount_percent' => 'decimal:2',
        ];
    }

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
            && (
                !$this->expires_at
                || $this->expires_at->isFuture()
            );
    }
}