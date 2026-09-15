<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'store_id',
        'service_id',
        'provider_id',
        'order_number',
        'provider_order_id',
        'target',
        'quantity',
        'provider_unit_cost',
        'provider_total_cost',
        'customer_unit_price',
        'customer_total_price',
        'profit_amount',
        'status',
        'provider_status',
        'payment_status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'provider_unit_cost' => 'decimal:4',
            'provider_total_cost' => 'decimal:4',
            'customer_unit_price' => 'decimal:4',
            'customer_total_price' => 'decimal:4',
            'profit_amount' => 'decimal:4',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
