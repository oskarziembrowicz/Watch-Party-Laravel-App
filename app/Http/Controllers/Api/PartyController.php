<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PartyController extends Controller
{
    public function list(Request $request) {
        $parties = Party::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'parties' => $parties->toArray(),
            ],
        ], 200);
    }

    public function access(Request $request, Party $party) {
        // Response
        return response()->json([
            'status' => 'success',
            'data' => [
                'party' => $party,
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'startDate'   => 'nullable|date',
            'isOnline'    => 'required|boolean',
            'joinLink'    => 'nullable|string',
            'address'     => 'nullable|string',
            'movies'      => 'nullable|array',
        ]);

        // Create party
        $party = Party::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date'  => $validated['startDate'],
            'is_online'   => $validated['isOnline'],
            'join_link'   => $validated['joinLink'] ?? null,
            'address'     => $validated['address'] ?? null,
            'movies'      => $validated['movies'] ?? [],
        ]);

        // Response
        return response()->json([
            'status' => 'success',
            'data' => [
                'party' => $party,
            ],
        ], 201);
    }

    // FIXME: This creates a new party
    public function update(Request $request, Party $party)
{
    // Allowed fields to update
    $allowedFields = [
        'name',
        'description',
        'startDate',
        'isOnline',
        'joinLink',
    ];

    if (!$party) {
        return response()->json([
            'status' => 'error',
            'message' => 'No party found with that ID',
        ], 404);
    }

    // Only update allowed fields
    foreach ($allowedFields as $field) {
        if ($request->has($field)) {
            // Convert camelCase to snake_case for DB
            $dbField = \Illuminate\Support\Str::snake($field);
            $party->$dbField = $request->input($field);
        }
    }

    $party->save(); // Persist changes

    return response()->json([
        'status' => 'success',
        'data' => [
            'updatedParty' => $party,
        ],
    ], 200);
}

}
