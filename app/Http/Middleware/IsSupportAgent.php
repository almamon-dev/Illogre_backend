<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSupportAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type === 'member' && auth()->user()->role === 'Support Agent') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. Support Agent access only.'], 403);
    }
}
