<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_name',
        'verification_status',
        'verified_at',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'is_default' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'bank_id');
    }
}
