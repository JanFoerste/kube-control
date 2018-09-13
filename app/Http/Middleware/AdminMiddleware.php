<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
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
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to perform this action.'
            ], 403);
        }

        return $next($request);
    }
}
