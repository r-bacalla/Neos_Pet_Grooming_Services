<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_stock_batch_id',
        'user_id',
        'sale_id',
        'type',
        'quantity',
        'batch_reference',
        'stock_before',
        'stock_after',
        'available_before',
        'available_after',
        'reason',
        'archived_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductStockBatch::class, 'product_stock_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'available_before' => 'integer',
            'available_after' => 'integer',
        ];
    }
}