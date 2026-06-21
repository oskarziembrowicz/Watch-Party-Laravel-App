<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PartyResource;
use App\Rules\SafeHttpUrl;

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

    /**
     * Display the party
     */
    public function show(Request $request, Party $party)
    {
        return new PartyResource($party);
    }

    // SECURITY: Any authenticated user can update any party, not just its author.
    // In production, verify $request->user()->id === $party->author_id.
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

    /**
     * End a party (set status to 'archived')
     * PATCH /parties/:id/end
     * SECURITY: Any authenticated user can end any party.
     * In production, restrict this to the party author.
     */
    public function endParty(Party $party)
    {
        $party->status = 'archived';
        $party->save();

        return new PartyResource($party);
    }

    /**
     * Remove party
     * SECURITY: Any authenticated user can delete any party.
     * In production, restrict deletion to the party author or an admin.
     */
    public function destroy(Party $party)
    {
        $party->delete();

        return response(status: 204);
    }

    /**
     * Add a participant
     * PATCH /parties/:id
     * SECURITY: Any authenticated user can add anyone to any party.
     * In production, restrict this to the party author, or require an invite flow.
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

    /**
     * Add a useful link
     * POST /parties/:id/useful-links
     */
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

    /**
     * Add a party impression
     * POST /parties/:id/impressions/party
     * SECURITY: Any authenticated user can leave an impression on any party, and
     * the userId is taken from the request body — not the authenticated user.
     * In production, derive userId from $request->user()->id and restrict to members.
     * Also enforce one impression per user.
     */
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

    /**
     * Add a movie impression
     * POST /parties/:id/impressions/movie
     * SECURITY: Same issues as addPartyImpression — userId is client-supplied and
     * there is no membership or duplicate check.
     * In production, derive userId server-side and enforce one impression per user per movie.
     */
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
