<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user && !$user->companies()->exists()) {
            if ($request->routeIs('filament.admin.auth.*') || $request->isMethod('POST') && str_contains($request->path(), 'logout')) {
                return $next($request);
            }
            
            $onboardingRoute = route('filament.admin.pages.onboarding');

            if (!$request->fullUrlIs($onboardingRoute)) {
                return redirect($onboardingRoute);
            }
        }

        return $next($request);
    }
}
