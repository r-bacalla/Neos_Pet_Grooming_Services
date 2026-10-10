<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'species',
        'included_add_on_ids',
        'available_add_on_ids',
        'description',
        'rate',
        'size',
        'duration_minutes',
        'active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'active' => 'boolean',
        'included_add_on_ids' => 'array',
        'available_add_on_ids' => 'array',
    ];

    public function groomingAppointments()
    {
        return $this->hasMany(GroomingAppointment::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

}