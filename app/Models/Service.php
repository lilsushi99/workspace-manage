<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'provider_service_id',
        'name',
        'slug',
        'platform',
        'category',
        'description',
        'base_price',
        'selling_price',
        'markup_type',
        'markup_value',
        'min_quantity',
        'max_quantity',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:4',
            'selling_price' => 'decimal:4',
            'markup_value' => 'decimal:4',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function providerService()
    {
        return $this->belongsTo(ProviderService::class);
    }

    public function tenantServices()
    {
        return $this->hasMany(TenantService::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
