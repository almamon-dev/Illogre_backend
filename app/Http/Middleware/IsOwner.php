<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type === 'owner') {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access. Only owners are allowed.'
        ], 403);
    }
}
