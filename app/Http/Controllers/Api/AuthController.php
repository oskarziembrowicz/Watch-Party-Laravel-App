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
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ])->withCookie(Cookie::forget('api_token'));
    }
}
