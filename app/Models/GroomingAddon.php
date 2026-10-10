<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroomingAddon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'description',
        'rate',
        'active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'active' => 'boolean',
    ];
}