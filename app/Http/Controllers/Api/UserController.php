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

    public function destroy(User $user)
    {
        $user->delete();
        return response(status: 204);
    }
}
