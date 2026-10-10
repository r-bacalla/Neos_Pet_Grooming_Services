<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroomingAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'pet_id',
        'pet_size',
        'visit_group_id',
        'service_id',
        'add_on_service_ids',
        'add_on_product_ids',
        'add_on_ids',
        'groomer_id',
        'groomer_name',
        'sale_id',

        // Old scheduling fields
        'scheduled_start',
        'scheduled_end',

        // Grooming workflow
        'status',
        'notes',
        'waiver_confirmed_at',
        'waiver_signature',
        'waiver_photo_path',
        'groomer_notes',
        'actual_weight',
        'grooming_price',

        // Status timestamps
        'checked_in_at',
        'started_at',
        'approved_at',
        'received_at',
        'ready_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
        'ready_at' => 'datetime',
        'completed_at' => 'datetime',
        'waiver_confirmed_at' => 'datetime',

        'actual_weight' => 'decimal:2',
        'grooming_price' => 'decimal:2',
        'add_on_service_ids' => 'array',
        'add_on_product_ids' => 'array',
        'add_on_ids' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function visitGroup()
    {
        return $this->belongsTo(GroomingVisitGroup::class, 'visit_group_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function groomer()
    {
        return $this->belongsTo(User::class, 'groomer_id');
    }

    public function assignedGroomerName(): ?string
    {
        return $this->groomer_name ?: $this->groomer?->name;
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}