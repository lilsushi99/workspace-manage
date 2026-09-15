<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customDomains()
    {
        return $this->hasMany(CustomDomain::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
