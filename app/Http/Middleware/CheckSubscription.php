<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isSubscribed()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Active subscription required to perform this action.'
        ], 403);
    }
}
