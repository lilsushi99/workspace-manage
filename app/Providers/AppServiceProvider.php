<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Bank\Adapters\FlutterwaveBankVerificationAdapter;
use App\Services\Bank\Contracts\BankVerificationInterface;
use App\Services\Payments\Adapters\FlutterwavePaymentGateway;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, FlutterwavePaymentGateway::class);
        $this->app->bind(BankVerificationInterface::class, FlutterwaveBankVerificationAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::define('access-admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('access-support', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'support']);
        });

        Gate::define('access-finance', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'finance']);
        });

        Gate::define('access-reseller', function (User $user) {
            return $user->hasRole('reseller');
        });
    }
}
