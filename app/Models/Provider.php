<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'api_url',
        'api_key',
        'status',
        'currency',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
        ];
    }

    public function providerServices()
    {
        return $this->hasMany(ProviderService::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
