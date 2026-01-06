<?php

namespace App\Http\Controllers\Api;

use App\Models\Party;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PartyController extends Controller
{
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

    public function list(Request $request) {
        $parties = Party::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'parties' => $parties->toArray(),
            ],
        ], 200);
    }
}
