<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictTo
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!in_array($request->user()?->role, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to perform this action',
            ], 403);
        }

        return $next($request);
    }
}
