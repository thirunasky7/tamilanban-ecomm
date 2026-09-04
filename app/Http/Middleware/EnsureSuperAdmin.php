<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin');

        if (! $user || ! $user->hasRole('super_admin')) {
            abort(403, 'Only super admin can access this area.');
        }

        return $next($request);
    }
}
