<?php

namespace App\Services\Providers;

use App\Models\Provider;
use App\Services\Providers\Adapters\ReallySimpleSocialProvider;
use App\Services\Providers\Contracts\SmmProviderInterface;
use App\Services\Providers\Exceptions\ProviderValidationException;

class ProviderManager
{
    public function make(Provider $provider): SmmProviderInterface
    {
        switch ($provider->slug) {
            case 'really-simple-social':
            default:
                return new ReallySimpleSocialProvider($provider);
        }
    }

    public function makeBySlug(string $slug): SmmProviderInterface
    {
        $provider = Provider::where('slug', $slug)->first();

        if (!$provider) {
            throw new ProviderValidationException("Provider with slug '{$slug}' not found.");
        }

        return $this->make($provider);
    }
}
