<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStockBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'batch_reference',
        'supplier',
        'received_at',
        'expiry_date',
        'inventory_quantity',
        'available_quantity',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'datetime',
            'inventory_quantity' => 'integer',
            'available_quantity' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_stock_batch_id');
    }
}