<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTenant
{
    /**
     * Handle an incoming request.
     * Checks if the authenticated user has 'tenant' role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($user->role !== 'tenant') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Tenant access required.',
            ], 403);
        }

        return $next($request);
    }
}


