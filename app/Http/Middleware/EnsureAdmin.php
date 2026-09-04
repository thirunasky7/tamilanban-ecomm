<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (! $user || ! $user->isAdmin() || ! $user->is_active) {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Please sign in with an admin account.',
            ]);
        }

        return $next($request);
    }
}
