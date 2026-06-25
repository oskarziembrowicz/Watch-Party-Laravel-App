<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Party;
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

    public function addFriend(Request $request)
    {
        $request->validate(['friendId' => 'required|exists:users,id']);

        $user = $request->user();
        $friendId = $request->friendId;

        $user->friends()->syncWithoutDetaching([$friendId]);

        return new UserResource($user->load('friends'));
    }

    public function removeFriend(Request $request, $id)
    {
        $user = $request->user();

        $user->friends()->detach($id);

        return response()->noContent();
    }

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

    public function removeMovie(Request $request, $id)
    {
        $user = $request->user();
        $movies = $user->saved_movies ?? [];
        $user->saved_movies = array_values(array_diff($movies, [$id]));
        $user->save();

        return response()->noContent();
    }

    public function hostedParties(User $user)
    {
        return $user->hostedParties()->get();
    }

    public function myHostedParties(Request $request)
    {
        return $request->user()->hostedParties()->get();
    }

    public function userParties(User $user)
    {
        return Party::whereHas('participants', fn($q) => $q->where('users.id', $user->id))->get();
    }

    public function myParties(Request $request)
    {
        $userId = $request->user()->id;
        return Party::whereHas('participants', fn($q) => $q->where('users.id', $userId))->get();
    }

    public function myArchivedParties(Request $request)
    {
        $userId = $request->user()->id;
        return Party::where('status', 'archived')
            ->whereHas('participants', fn($q) => $q->where('users.id', $userId))
            ->get();
    }
}
