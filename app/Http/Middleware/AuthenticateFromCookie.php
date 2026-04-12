<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken() && $token = $request->cookie('api_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
