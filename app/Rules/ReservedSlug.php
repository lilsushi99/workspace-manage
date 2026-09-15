<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ReservedSlug implements ValidationRule
{
    protected array $reserved = [
        'admin',
        'administrator',
        'login',
        'logout',
        'register',
        'dashboard',
        'api',
        'settings',
        'store',
        'stores',
        'onboarding',
        'auth',
        'user',
        'users',
        'billing',
        'support',
        'help',
        'app',
        'system',
        'root',
        'dowa',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = Str::slug($value);

        if (in_array($slug, $this->reserved, true)) {
            $fail("The {$attribute} '{$value}' is a reserved system keyword and cannot be used.");
        }
    }
}
