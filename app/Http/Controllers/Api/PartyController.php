<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PartyResource;
use App\Rules\SafeHttpUrl;

class PartyController extends Controller
{
    public function index(Request $request)
    {
        return PartyResource::collection(Party::latest()->get());
    }

    public function store(Request $request)
    {
        // Create party
        $party = Party::create([
            ...$request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
                'startDate'   => 'nullable|date',
                'isOnline'    => 'required|boolean',
                'joinLink'    =>
                [
                    'nullable',
                    'string',
                    new SafeHttpUrl()
                ],
                'address'     => 'nullable|string',
                'movies'      => 'nullable|array',
                'status'      => 'sometimes|in:expected,ongoing,archived',
            ]),
            'author_id' => $request->user()->id,
        ]);

        // Add author as first participant
        $party->participants()->attach($request->user()->id);

        return new PartyResource($party);
    }

    public function show(Request $request, Party $party)
    {
        return new PartyResource($party);
    }

    public function update(Request $request, Party $party)
    {
        $party->update(
            $request->validate([
                'name'        => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'startDate'   => 'nullable|date',
                'isOnline'    => 'sometimes|boolean',
                'joinLink'    =>
                [
                    'nullable',
                    'string',
                    new SafeHttpUrl()
                ],
                'address'     => 'nullable|string',
                'movies'      => 'nullable|array',
                'status'      => 'sometimes|in:expected,ongoing,archived',
            ])
        );

        return new PartyResource($party);
    }

    public function endParty(Party $party)
    {
        $party->status = 'archived';
        $party->save();

        return new PartyResource($party);
    }

    public function destroy(Party $party)
    {
        $party->delete();

        return response(status: 204);
    }

    public function addParticipant(Request $request, Party $party)
    {
        $request->validate(['userId' => 'required|exists:users,id']);

        $party->participants()->syncWithoutDetaching([$request->userId]);

        return new PartyResource($party->load('participants'));
    }

    public function removeParticipant($partyId, $id)
    {
        $party = Party::findOrFail($partyId);

        $party->participants()->detach($id);

        return response()->noContent();
    }

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

    public function removeMovie($partyId, $id)
    {
        $party = Party::findOrFail($partyId);

        $movies = $party->movies ?? [];
        $party->movies = array_values(array_diff($movies, [$id]));
        $party->save();

        return response()->noContent();
    }

    public function addUsefulLink(Request $request, Party $party)
    {
        $data = $request->validate([
            'link' => ['required', 'string', new SafeHttpUrl()],
        ]);

        $links = $party->useful_links ?? [];
        $links[] = $data['link'];
        $party->useful_links = $links;
        $party->save();

        return new PartyResource($party);
    }

    public function addPartyImpression(Request $request, Party $party)
    {
        $data = $request->validate([
            'userId'     => 'required|exists:users,id',
            'impression' => 'required|string',
        ]);

        $impressions = $party->party_impressions ?? [];
        $impressions[] = [
            'user_id'    => $data['userId'],
            'impression' => $data['impression'],
        ];
        $party->party_impressions = $impressions;
        $party->save();

        return new PartyResource($party);
    }

    public function addMovieImpression(Request $request, Party $party)
    {
        $data = $request->validate([
            'movieId'    => 'required|string',
            'userId'     => 'required|exists:users,id',
            'impression' => 'required|string',
        ]);

        $impressions = $party->movie_impressions ?? [];
        $impressions[] = [
            'movie_id'   => $data['movieId'],
            'user_id'    => $data['userId'],
            'impression' => $data['impression'],
        ];
        $party->movie_impressions = $impressions;
        $party->save();

        return new PartyResource($party);
    }
}
