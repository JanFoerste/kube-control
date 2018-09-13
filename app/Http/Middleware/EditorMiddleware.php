<?php

namespace App\Http\Middleware;

use Closure;

class EditorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!\in_array($request->user()->role, ['admin', 'editor'], true)) {
            return response()->json([
                'error' => 'You are not authorized to perform this action.'
            ], 403);
        }

        return $next($request);
    }
}
