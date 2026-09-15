<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'external_service_id',
        'name',
        'category',
        'platform',
        'description',
        'min_quantity',
        'max_quantity',
        'provider_price',
        'provider_currency',
        'supports_refill',
        'supports_cancel',
        'supports_drip_feed',
        'status',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'provider_price' => 'decimal:4',
            'supports_refill' => 'boolean',
            'supports_cancel' => 'boolean',
            'supports_drip_feed' => 'boolean',
            'raw_response' => 'array',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function internalServices()
    {
        return $this->hasMany(Service::class);
    }
}
