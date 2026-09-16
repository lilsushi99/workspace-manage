<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Reseller\OrderController as ResellerOrderController;
use App\Http\Controllers\Reseller\ServiceController as ResellerServiceController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Http\Middleware\EnsureOnboardingIsIncomplete;
use Illuminate\Support\Facades\Route;

// Public Marketing Routes
Route::get('/', [MarketingController::class, 'index'])->name('home');
Route::get('/features', [MarketingController::class, 'features'])->name('marketing.features');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('marketing.pricing');
Route::get('/resources', [MarketingController::class, 'resources'])->name('marketing.resources');
Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');

// Public Storefront Routes
Route::get('/store/{username}', [StorefrontController::class, 'show'])->name('storefront.show');
Route::post('/store/{username}/summary', [StorefrontController::class, 'calculateSummary'])->name('storefront.summary');
Route::get('/store/{username}/checkout', [CheckoutController::class, 'showCheckout'])->name('storefront.checkout');
Route::post('/store/{username}/checkout', [CheckoutController::class, 'processCheckout'])->name('storefront.checkout.process');
Route::get('/store/{username}/payment-status', [StorefrontController::class, 'paymentStatus'])->name('storefront.payment-status');

// Webhook Endpoints
Route::post('/webhooks/flutterwave', [WebhookController::class, 'handleFlutterwave'])->name('webhooks.flutterwave');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers.index');
        Route::get('/providers/{provider}/edit', [AdminProviderController::class, 'edit'])->name('providers.edit');
        Route::put('/providers/{provider}', [AdminProviderController::class, 'update'])->name('providers.update');
        Route::post('/providers/{provider}/test', [AdminProviderController::class, 'testConnection'])->name('providers.test');
        Route::post('/providers/{provider}/sync', [AdminProviderController::class, 'sync'])->name('providers.sync');

        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [AdminServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [AdminServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/retry', [AdminOrderController::class, 'retry'])->name('orders.retry');
    });

    // Reseller Service & Pricing Management (Requires complete onboarding)
    Route::middleware([EnsureOnboardingIsComplete::class])->prefix('reseller')->name('reseller.')->group(function () {
        Route::get('/services', [ResellerServiceController::class, 'index'])->name('services.index');
        Route::post('/services/{service}/toggle', [ResellerServiceController::class, 'toggle'])->name('services.toggle');
        Route::get('/services/{tenantService}/edit', [ResellerServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{tenantService}', [ResellerServiceController::class, 'update'])->name('services.update');

        Route::get('/orders', [ResellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [ResellerOrderController::class, 'show'])->name('orders.show');
    });

    // Onboarding Wizard Routes (Incomplete onboarding only)
    Route::middleware([EnsureOnboardingIsIncomplete::class])->prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [OnboardingController::class, 'index'])->name('index');
        Route::get('/account', [OnboardingController::class, 'showAccount'])->name('account');
        Route::post('/account', [OnboardingController::class, 'postAccount']);
        Route::get('/services', [OnboardingController::class, 'showServices'])->name('services');
        Route::post('/services', [OnboardingController::class, 'postServices']);
        Route::get('/pricing', [OnboardingController::class, 'showPricing'])->name('pricing');
        Route::post('/pricing', [OnboardingController::class, 'postPricing']);
        Route::get('/store', [OnboardingController::class, 'showStore'])->name('store');
        Route::post('/store', [OnboardingController::class, 'postStore']);
        Route::get('/bank', [OnboardingController::class, 'showBank'])->name('bank');
        Route::post('/bank', [OnboardingController::class, 'postBank']);
        Route::get('/review', [OnboardingController::class, 'showReview'])->name('review');
        Route::post('/review', [OnboardingController::class, 'postReview']);
    });

    // Completion Screen
    Route::get('/onboarding/complete', [OnboardingController::class, 'showComplete'])->name('onboarding.complete');

    // Dashboard (Requires complete onboarding)
    Route::middleware([EnsureOnboardingIsComplete::class])->get('/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to Dowa Reseller Dashboard',
            'user' => auth()->user()->only(['id', 'first_name', 'last_name', 'email', 'username']),
            'tenant' => auth()->user()->tenant ? auth()->user()->tenant->only(['id', 'name', 'slug', 'status', 'onboarding_step']) : null,
        ]);
    })->name('dashboard');
});
