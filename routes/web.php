<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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
    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to Dowa Dashboard Foundation',
            'user' => auth()->user()->only(['id', 'first_name', 'last_name', 'email', 'username']),
            'tenant' => auth()->user()->tenant ? auth()->user()->tenant->only(['id', 'name', 'slug', 'status']) : null,
        ]);
    })->name('dashboard');
});
