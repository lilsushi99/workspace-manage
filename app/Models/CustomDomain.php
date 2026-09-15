<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'domain',
        'verification_token',
        'verified_at',
        'ssl_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
