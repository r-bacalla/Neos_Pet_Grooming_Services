<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroomingPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'min_weight',
        'max_weight',
        'price',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'min_weight' => 'decimal:2',
            'max_weight' => 'decimal:2',
            'price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function appliesTo(float $weight): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($weight < (float) $this->min_weight) {
            return false;
        }

        if (
            $this->max_weight !== null &&
            $weight > (float) $this->max_weight
        ) {
            return false;
        }

        return true;
    }

    public function categoryLabel(): string
    {
        if ((float) $this->max_weight <= 5) {
            return 'Small';
        }

        if ((float) $this->min_weight > 5 && (float) ($this->max_weight ?? 0) <= 15) {
            return 'Medium';
        }

        return 'Large';
    }

    public static function findForCategory(?string $category): ?self
    {
        $referenceWeight = match ($category) {
            'Small' => 5,
            'Medium' => 10,
            'Large' => 20,
            default => null,
        };

        return $referenceWeight === null
            ? null
            : static::findForWeight($referenceWeight);
    }

    public static function findForWeight(float $weight): ?self
    {
        return static::query()
            ->where('active', true)
            ->where('min_weight', '<=', $weight)
            ->where(function ($query) use ($weight) {
                $query->whereNull('max_weight')
                    ->orWhere('max_weight', '>=', $weight);
            })
            ->orderBy('min_weight')
            ->first();
    }

}