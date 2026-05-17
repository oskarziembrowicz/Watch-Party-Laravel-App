<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        // SECURITY: Password is stored in plain text — no hashing is applied.
        // In production, use Hash::make() before storing, and Hash::check() when verifying.

        // SECURITY: No password complexity rules (minimum length, character requirements).
        // In production, add: 'password' => 'required|min:8|...' constraints.

        // SECURITY: No rate limiting on signup — the endpoint can be abused for
        // mass account creation. In production, apply throttle middleware.

        // SECURITY: The full user object (including role) is returned in the response.
        // In production, return only safe fields via a UserResource.

        $user = User::create([
            ...$request->validate([
                'username' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
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
        // SECURITY: No rate limiting or account lockout after repeated failures.
        // In production, apply throttle middleware and temporarily lock accounts
        // after N failed attempts to prevent brute-force attacks.

        // SECURITY: Tokens are not scoped — the created token has access to all
        // abilities. In production, use named abilities to limit token permissions.

        // SECURITY: Old tokens are not invalidated on new login — a user can
        // accumulate unlimited active tokens. In production, revoke previous tokens
        // or limit to one active token per user.

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect']
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect']
            ]);
        }

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
