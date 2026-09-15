<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'business_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $resellerRole = Role::firstOrCreate(
            ['slug' => 'reseller'],
            ['name' => 'Reseller', 'description' => 'Reseller Account']
        );
        $user->roles()->attach($resellerRole);

        $businessName = $request->business_name ?: ($request->first_name . "'s Business");
        $tenant = Tenant::create([
            'owner_user_id' => $user->id,
            'name' => $businessName,
            'slug' => Str::slug($businessName) . '-' . Str::random(5),
            'status' => 'active',
            'currency' => 'USD',
            'timezone' => 'UTC',
        ]);

        Wallet::create([
            'tenant_id' => $tenant->id,
            'currency' => 'USD',
            'balance' => 0,
            'available_balance' => 0,
            'pending_balance' => 0,
        ]);

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
