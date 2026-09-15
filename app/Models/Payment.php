<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_id',
        'reference',
        'provider_reference',
        'gateway',
        'amount',
        'currency',
        'status',
        'payment_method',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'paid_at' => 'datetime',
            'metadata' => 'array',
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
