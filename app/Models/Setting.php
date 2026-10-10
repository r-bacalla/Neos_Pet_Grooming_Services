<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    public static function expiryWarningDays(): int
    {
        return (int) static::getValue('expiry_warning_days', 10);
    }

    public static function expiryDiscountPercent(): float
    {
        return (float) static::getValue('expiry_discount_percent', 10);
    }

    public static function expiryDiscountEnabled(): bool
    {
        return (bool) ((int) static::getValue(
            'expiry_discount_enabled',
            1
        ));
    }
}