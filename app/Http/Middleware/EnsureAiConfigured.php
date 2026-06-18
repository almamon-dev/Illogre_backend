<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiConfigured
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isAiConfigured()) {
            return response()->json([
                'success' => false,
                'error_code' => 'AI_NOT_CONFIGURED',
                'message' => 'AI services are currently inactive. Please contact the platform administrator to enable AI functionality.',
                'user_type' => $user->user_type,
            ], 403);
        }

        return $next($request);
    }
}
