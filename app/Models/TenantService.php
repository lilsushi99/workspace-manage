<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantService extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'service_id',
        'selling_price',
        'markup_type',
        'markup_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:4',
            'markup_value' => 'decimal:4',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
