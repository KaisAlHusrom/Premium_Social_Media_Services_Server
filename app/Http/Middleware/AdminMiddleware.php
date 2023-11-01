<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated
        if (auth()->check()) {
            // Check if the user is an admin (is_admin is true)
            if (auth()->user()->is_admin) {
                // Allow access to the requested route
                return $next($request);
            }
        }

        // If the user is not an admin, you can customize the response or redirect as needed.
        // For example, you can return a forbidden response:
        return response('Unauthorized', 403);
    }
}
