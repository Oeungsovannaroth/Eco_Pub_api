<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Supports:
     * - role:any          → Any logged-in user (customer, staff, admin)
     * - role:admin,staff  → Specific roles only
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Please log in to access this resource.',
            ], 401);
        }

        
        if (empty($roles) || in_array('any', $roles)) {
            return $next($request);
        }

        
        foreach ($roles as $role) {
            if (strtolower($user->role) === strtolower($role)) {  
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Forbidden. You do not have permission to access this resource.',
        ], 403);
        // return response()->json([
        //     'message' => 'Forbidden. You do not have permission to access this resource.' + $role,
        // ], 401);
    }
}