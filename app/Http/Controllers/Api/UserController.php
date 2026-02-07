<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return UserResource::collection(User::latest()->get());
    }

    public function show(Request $request, User $user)
    {
        return new UserResource($user);
    }

    public function getMe(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(Request $request, User $user)
    {
        $user->update(
            $request->validate([
                'username' => 'sometimes|string',
                'email' => 'sometimes|email',
            ])
        );

        return new UserResource($user);
    }

    public function updateMe(Request $request)
    {
        $request->user()->update(
            $request->validate([
                'username' => 'sometimes|string',
                'email' => 'sometimes|email',
            ])
        );

        return new UserResource($request->user());
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response(status: 204);
    }

    public function destroyMe(Request $request)
    {
        $request->user()->tokens()->delete();

        $request->user()->delete();

        return response(status: 204);
    }
}
