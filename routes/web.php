<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\OnboardingController;
use App\Http\Middleware\EnsureOnboardingIsComplete;
use App\Http\Middleware\EnsureOnboardingIsIncomplete;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
