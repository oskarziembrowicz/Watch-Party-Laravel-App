<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PartyResource;

class PartyController extends Controller
{
    /**
     * Display a listing parties.
     */
    public function index(Request $request)
    {
        return PartyResource::collection(Party::latest()->get());
    }

    /**
     * Store a newly created party in storage.
     */
    public function store(Request $request)
    {
        // Create party
        $party = Party::create([
            ...$request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
                'startDate'   => 'nullable|date',
                'isOnline'    => 'required|boolean',
                'joinLink'    => 'nullable|string',
                'address'     => 'nullable|string',
                'movies'      => 'nullable|array',
            ]),
            'author_id' => $request->user()->id,
        ]);

        // Add author as first participant
        $party->participants()->attach($request->user()->id);

        return new PartyResource($party);
    }

    /**
     * Display the party
     */
    public function show(Request $request, Party $party)
    {
        return new PartyResource($party);
    }

    // FIXME: This creates a new party
    public function update(Request $request, Party $party)
    {
        $party->update(
            $request->validate([
                'name'        => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'startDate'   => 'nullable|date',
                'isOnline'    => 'sometimes|boolean',
                'joinLink'    => 'nullable|string',
                'address'     => 'nullable|string',
                'movies'      => 'nullable|array',
            ])
        );

        return new PartyResource($party);
    }

    /**
     * Remove party
     */
    public function destroy(Party $party)
    {
        $party->delete();

        return response(status: 204);
    }

    /**
     * Add a participant
     * PATCH /parties/:id
     */
    public function addParticipant(Request $request, Party $party)
    {
        $request->validate(['userId' => 'required|exists:users,id']);

        $party->participants()->syncWithoutDetaching([$request->userId]);

        return new PartyResource($party->load('participants'));
    }

    /**
     * Remove a participant
     * DELETE /parties/:partyId/participants/:id
     */
    public function removeParticipant($partyId, $id)
    {
        $party = Party::findOrFail($partyId);

        $party->participants()->detach($id);

        return response()->noContent();
    }

    /**
     * Add a movie
     * PATCH /parties/:id/movies
     */
    public function addMovie(Request $request, Party $party)
    {
        $request->validate(['movieId' => 'required|string']);

        $movies = $party->movies ?? [];
        if (!in_array($request->movieId, $movies)) {
            $movies[] = $request->movieId;
        }
        $party->movies = $movies;
        $party->save();

        return new PartyResource($party);
    }

    /**
     * Remove a movie
     * DELETE /parties/:partyId/movies/:id
     */
    public function removeMovie($partyId, $id)
    {
        $party = Party::findOrFail($partyId);

        $movies = $party->movies ?? [];
        $party->movies = array_values(array_diff($movies, [$id]));
        $party->save();

        return response()->noContent();
    }
}
