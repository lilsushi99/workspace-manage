<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'currency',
        'balance',
        'available_balance',
        'pending_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:4',
            'available_balance' => 'decimal:4',
            'pending_balance' => 'decimal:4',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
