<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::created(function (Product $product) {
            if ((int) $product->stock > 0 || (int) $product->available_stock > 0) {
                $product->stockBatches()->create([
                    'batch_reference' => 'LEGACY-' . $product->sku,
                    'expiry_date' => $product->expiry_date,
                    'inventory_quantity' => (int) $product->stock,
                    'available_quantity' => (int) $product->available_stock,
                ]);
            }
        });
    }

    protected $fillable = [
        'name',
        'sku',
        'category',
        'cost',
        'price',
        'stock',
        'available_stock',
        'last_stock_in_quantity',
        'last_stock_in_at',
        'low_stock_level',
        'expiry_date',
        'expiry_discount',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'available_stock' => 'integer',
            'last_stock_in_quantity' => 'integer',
            'last_stock_in_at' => 'datetime',
            'low_stock_level' => 'integer',
            'expiry_date' => 'date',
            'expiry_discount' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        if ($this->exists && $this->relationLoaded('stockBatches')) {
            $remainingBatches = $this->stockBatches->filter(fn (ProductStockBatch $batch) =>
                $batch->inventory_quantity > 0 || $batch->available_quantity > 0
            );

            if ($remainingBatches->isNotEmpty()) {
                return $remainingBatches->every(fn (ProductStockBatch $batch) =>
                    $batch->expiry_date && $batch->expiry_date->lt(today())
                );
            }
        } elseif ($this->exists && $this->stockBatches()->exists()) {
            $remainingBatches = $this->stockBatches()
                ->where(function ($query) {
                    $query->where('inventory_quantity', '>', 0)
                        ->orWhere('available_quantity', '>', 0);
                });

            if ($remainingBatches->exists()) {
                return !$remainingBatches
                    ->where(function ($query) {
                        $query->whereNull('expiry_date')
                            ->orWhereDate('expiry_date', '>=', today());
                    })
                    ->exists();
            }
        }

        return $this->expiry_date?->lt(today()) ?? false;
    }

    public function hasExpiredStockBatches(): bool
    {
        $batches = $this->relationLoaded('stockBatches')
            ? $this->stockBatches
            : $this->stockBatches()->get();

        return $batches->contains(fn (ProductStockBatch $batch) =>
            $batch->expiry_date &&
            $batch->expiry_date->lt(today()) &&
            ($batch->inventory_quantity > 0 || $batch->available_quantity > 0)
        );
    }

    public function hasNearExpiryStockBatches(): bool
    {
        $batches = $this->relationLoaded('stockBatches')
            ? $this->stockBatches
            : $this->stockBatches()->get();
        $today = today();
        $warningEnd = $today->copy()->addDays(Setting::expiryWarningDays());

        $remainingBatches = $batches->filter(fn (ProductStockBatch $batch) =>
            $batch->inventory_quantity > 0 || $batch->available_quantity > 0
        );
        $datedBatches = $remainingBatches->filter(fn (ProductStockBatch $batch) => $batch->expiry_date);

        if ($datedBatches->isNotEmpty()) {
            return $datedBatches->contains(fn (ProductStockBatch $batch) =>
                $batch->expiry_date->gte($today) &&
                $batch->expiry_date->lte($warningEnd)
            );
        }

        return ($remainingBatches->isNotEmpty() || $this->stock > 0 || $this->available_stock > 0) &&
            $this->expiry_date &&
            $this->expiry_date->gte($today) &&
            $this->expiry_date->lte($warningEnd);
    }

    public function isNearExpiry(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        if (!Setting::expiryDiscountEnabled()) {
            return false;
        }

        $warningDays = Setting::expiryWarningDays();

        if ($this->exists && $this->stockBatches()->exists()) {
            return $this->stockBatches()
                ->where('available_quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', today())
                ->whereDate('expiry_date', '<=', today()->addDays($warningDays))
                ->exists();
        }

        if (!$this->expiry_date) {
            return false;
        }

        return now()->startOfDay()
            ->diffInDays($this->expiry_date->startOfDay(), false) <= $warningDays;
    }

    public function automaticDiscountPercent(): float
    {
        if (!$this->isNearExpiry()) {
            return 0;
        }

        return (float) Setting::expiryDiscountPercent();
    }

    public function batchDiscountPercent(ProductStockBatch $batch): float
    {
        if (!$batch->expiry_date || $batch->expiry_date->lt(today()) || !Setting::expiryDiscountEnabled()) {
            return 0;
        }

        return today()->diffInDays($batch->expiry_date, false) <= Setting::expiryWarningDays()
            ? (float) Setting::expiryDiscountPercent()
            : 0;
    }

    public function availableSaleBatches()
    {
        $batches = $this->relationLoaded('stockBatches')
            ? $this->stockBatches
            : $this->stockBatches()->get();

        return $batches
            ->filter(fn (ProductStockBatch $batch) =>
                $batch->available_quantity > 0 &&
                (!$batch->expiry_date || !$batch->expiry_date->lt(today()))
            )
            ->sortBy([
                fn (ProductStockBatch $batch) => $batch->expiry_date?->format('Y-m-d') ?? '9999-12-31',
                fn (ProductStockBatch $batch) => $batch->id,
            ])
            ->values();
    }

    public function salePricingBreakdown()
    {
        return $this->availableSaleBatches()->map(function (ProductStockBatch $batch) {
            $discountPercent = $this->batchDiscountPercent($batch);

            return [
                'batch_id' => $batch->id,
                'quantity' => (int) $batch->available_quantity,
                'original_price' => (float) $this->price,
                'unit_price' => round((float) $this->price * (1 - $discountPercent / 100), 2),
                'discount_percent' => $discountPercent,
            ];
        });
    }

    public function discountedPrice(): float
    {
        $price = (float) $this->price;
        $discountPercent = $this->automaticDiscountPercent();

        if ($discountPercent <= 0) {
            return $price;
        }

        return round(
            $price - ($price * ($discountPercent / 100)),
            2
        );
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockBatches()
    {
        return $this->hasMany(ProductStockBatch::class);
    }

    public function syncStockTotals(): void
    {
        $inventoryQuantity = $this->stockBatches()->sum('inventory_quantity');
        $availableQuantity = $this->stockBatches()->sum('available_quantity');
        $expiryDate = $this->stockBatches()
            ->where(function ($query) {
                $query->where('inventory_quantity', '>', 0)
                    ->orWhere('available_quantity', '>', 0);
            })
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', today())
            ->orderBy('expiry_date')
            ->value('expiry_date');

        if (!$expiryDate) {
            $expiryDate = $this->stockBatches()
                ->where(function ($query) {
                    $query->where('inventory_quantity', '>', 0)
                        ->orWhere('available_quantity', '>', 0);
                })
                ->whereNotNull('expiry_date')
                ->orderBy('expiry_date')
                ->value('expiry_date');
        }

        if ($inventoryQuantity === 0 && $availableQuantity === 0) {
            $expiryDate = $this->expiry_date?->toDateString();
        }

        $this->forceFill([
            'stock' => $inventoryQuantity,
            'available_stock' => $availableQuantity,
            'expiry_date' => $expiryDate,
        ])->saveQuietly();
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function potentialSellingValue(): float
    {
        return round((float) $this->price * (int) $this->stock, 2);
    }

    public function inventoryValue(): float
    {
        return round((float) $this->cost * (int) $this->stock, 2);
    }
}