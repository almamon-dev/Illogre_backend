<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSupportManager
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type === 'member' && auth()->user()->role === 'Support Manager') {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access. Only Support Managers are allowed.'
        ], 403);
    }
}
