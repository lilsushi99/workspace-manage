<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingIsIncomplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant && $user->tenant->isOnboardingComplete()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
