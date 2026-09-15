<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'wallet_id',
        'tenant_id',
        'reference',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'direction',
        'status',
        'order_id',
        'withdrawal_id',
        'payment_id',
        'description',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'balance_before' => 'decimal:4',
            'balance_after' => 'decimal:4',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
