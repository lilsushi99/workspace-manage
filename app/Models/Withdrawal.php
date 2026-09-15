<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'wallet_id',
        'bank_id',
        'reference',
        'amount',
        'fee',
        'net_amount',
        'account_number',
        'account_name',
        'status',
        'requested_at',
        'processed_at',
        'processed_by',
        'rejection_reason',
        'provider_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'fee' => 'decimal:4',
            'net_amount' => 'decimal:4',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
