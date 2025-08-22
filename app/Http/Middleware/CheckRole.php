<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Comma-separated list of roles (e.g., 'admin,developer')
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Parse roles from parameter
        $requiredRoles = array_map('trim', explode(',', $roles));
        
        // Check if user has any of the required roles
        if (!$request->user()->hasAnyRole($requiredRoles)) {
            abort(403, 'Insufficient permissions to access this resource.');
        }

        return $next($request);
    }
}
