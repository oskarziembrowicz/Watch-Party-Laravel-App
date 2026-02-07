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

    /**
     * Add a friend
     * PUT /users/me/friends
     */
    public function addFriend(Request $request)
    {
        $request->validate(['friendId' => 'required|exists:users,id']);

        $user = $request->user();
        $friendId = $request->friendId;

        $user->friends()->syncWithoutDetaching([$friendId]);

        return new UserResource($user->load('friends'));
    }

    /**
     * Remove a friend
     * DELETE /users/me/friends/:id
     */
    public function removeFriend(Request $request, $id)
    {
        $user = $request->user();

        $user->friends()->detach($id);

        return response()->noContent();
    }

    /**
     * Add a movie
     * PUT /users/me/movies
     */
    public function addMovie(Request $request)
    {
        $request->validate(['movieId' => 'required|string']);

        $user = $request->user();
        $movies = $user->saved_movies ?? [];
        if (!in_array($request->movieId, $movies)) {
            $movies[] = $request->movieId;
        }
        $user->saved_movies = $movies;
        $user->save();

        return new UserResource($user);
    }

    /**
     * Remove a movie
     * DELETE /users/me/movies/:id
     */
    public function removeMovie(Request $request, $id)
    {
        $user = $request->user();
        $movies = $user->saved_movies ?? [];
        $user->saved_movies = array_values(array_diff($movies, [$id]));
        $user->save();

        return response()->noContent();
    }

    /**
     * Get a user's hosted parties
     * GET /users/:id/hosted-parties
     */
    public function hostedParties(User $user)
    {
        return $user->hostedParties()->get();
    }

    /**
     * Get my hosted parties
     * GET /users/me/hosted-parties
     */
    public function myHostedParties(Request $request)
    {
        return $request->user()->hostedParties()->get();
    }

    /**
     * Get a user's participating parties
     * GET /users/:id/parties
     */
    public function userParties(User $user)
    {
        return $user->parties()->get();
    }

    /**
     * Get my participating parties
     * GET /users/me/parties
     */
    public function myParties(Request $request)
    {
        return $request->user()->parties()->get();
    }
}
