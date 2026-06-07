<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RestrictTo
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!in_array($request->user()?->role, $roles)) {
            Log::warning('ACCESS_DENIED', [
                'event' => 'forbidden',
                'user_id' => $request->user()?->id,
                'role' => $request->user()?->role,
                'required_roles' => $roles,
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to perform this action',
            ], 403);
        }

        return $next($request);
    }
}
