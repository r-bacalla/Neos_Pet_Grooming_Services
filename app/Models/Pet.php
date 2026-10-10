<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'species',
        'breed',
        'sex',
        'birth_date',
        'weight',
        'weight_category',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'weight' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function groomingAppointments()
    {
        return $this->hasMany(GroomingAppointment::class);
    }
}