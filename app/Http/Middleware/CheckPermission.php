<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if the user is logged in and if they have the required permission slug.
        if (!Auth::check() || !$request->user()->hasPermissionTo($permission)) {
            // If not, return a 403 Forbidden error response.
            return response()->json(['message' => 'Forbidden: You do not have the required permission.'], 403);
        }

        return $next($request);
    }
}
