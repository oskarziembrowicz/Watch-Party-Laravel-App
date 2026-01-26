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
        // $parties = Party::latest()->get();

        // return response()->json([
        //     'status' => 'success',
        //     'data' => [
        //         'parties' => $parties->toArray(),
        //     ],
        // ], 200);

        return PartyResource::collection(Party::latest()->get());
    }

    /**
     * Store a newly created party in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        // $validated = $request->validate([
        //     'name'        => 'required|string|max:255',
        //     'description' => 'nullable|string',
        //     'startDate'   => 'nullable|date',
        //     'isOnline'    => 'required|boolean',
        //     'joinLink'    => 'nullable|string',
        //     'address'     => 'nullable|string',
        //     'movies'      => 'nullable|array',
        // ]);

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
            ])
        ]);

        return new PartyResource($party);

        // Response
        // return response()->json([
        //     'status' => 'success',
        //     'data' => [
        //         'party' => $party,
        //     ],
        // ], 201);
    }

    /**
     * Display the party
     */
    public function show(Request $request, Party $party)
    {
        return new PartyResource($party);
        // Response
        // return response()->json([
        //     'status' => 'success',
        //     'data' => [
        //         'party' => $party,
        //     ],
        // ], 200);
    }

    // FIXME: This creates a new party
    public function update(Request $request, Party $party)
    {
        // // Allowed fields to update
        // $allowedFields = [
        //     'name',
        //     'description',
        //     'startDate',
        //     'isOnline',
        //     'joinLink',
        // ];

        // if (!$party) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'No party found with that ID',
        //     ], 404);
        // }

        // // Only update allowed fields
        // foreach ($allowedFields as $field) {
        //     if ($request->has($field)) {
        //         // Convert camelCase to snake_case for DB
        //         $dbField = \Illuminate\Support\Str::snake($field);
        //         $party->$dbField = $request->input($field);
        //     }
        // }

        // $party->save(); // Persist changes

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

        // TODO: Keep for comparison of basic approach
        // return response()->json([
        //     'status' => 'success',
        //     'data' => [
        //         'updatedParty' => $party,
        //     ],
        // ], 200);
    }

    /**
     * Remove party
     */
    public function destroy(Party $party)
    {
        $party->delete();

        return response(status: 204);
    }
}
