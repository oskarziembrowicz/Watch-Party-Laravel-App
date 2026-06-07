<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        // SECURITY: The full user object (including role) is returned in the response.
        // In production, return only safe fields via a UserResource.

        $user = User::create([
            ...$request->validate([
                'username' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
            ])
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201)->withCookie(cookie('api_token', $token, 60 * 24 * 7));
    }

    public function login(Request $request)
    {
        // SECURITY: No per-account lockout after repeated failures.
        // In production, temporarily lock accounts after N failed attempts.

        // SECURITY: Tokens are not scoped — the created token has access to all
        // abilities. In production, use named abilities to limit token permissions.

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning('AUTH_FAILURE', [
                'event' => 'login_failed',
                'reason' => 'user_not_found',
                'ip' => $request->ip(),
                // email is low-sensitivity context; password is never logged
            ]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect']
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            Log::warning('AUTH_FAILURE', [
                'event' => 'login_failed',
                'reason' => 'wrong_password',
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect']
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token
        ])->withCookie(cookie('api_token', $token, 60 * 24 * 7, '/', null, config('session.secure'), true, false, 'Strict'));
    }

    public function logout(Request $request)
    {
        // SECURITY: All tokens for the user are revoked, not just the current one.
        // This is acceptable but means other devices are also logged out silently.
        // In production, consider revoking only the current token: $request->user()->currentAccessToken()->delete();
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ])->withCookie(Cookie::forget('api_token'));
    }
}
