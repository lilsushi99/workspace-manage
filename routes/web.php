<?php

use App\Http\Controllers\Admin\ProviderController as AdminProviderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\OnboardingController;
use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Http\Middleware\EnsureOnboardingIsIncomplete;
use Illuminate\Support\Facades\Route;

// Public Marketing Routes
Route::get('/', [MarketingController::class, 'index'])->name('home');
Route::get('/features', [MarketingController::class, 'features'])->name('marketing.features');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('marketing.pricing');
Route::get('/resources', [MarketingController::class, 'resources'])->name('marketing.resources');
Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Provider Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers.index');
        Route::get('/providers/{provider}/edit', [AdminProviderController::class, 'edit'])->name('providers.edit');
        Route::put('/providers/{provider}', [AdminProviderController::class, 'update'])->name('providers.update');
        Route::post('/providers/{provider}/test', [AdminProviderController::class, 'testConnection'])->name('providers.test');
        Route::post('/providers/{provider}/sync', [AdminProviderController::class, 'sync'])->name('providers.sync');
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
