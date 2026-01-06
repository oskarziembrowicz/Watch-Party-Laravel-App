<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class MovieController extends Controller
{
    public function getMovie(Request $request)
    {
        // Get the title from request
        $title = $request->query('title');

        // Return error of no title
        if (!$title) {
            return response()->json([
                'status' => 'error',
                'message' => 'title query parameter is required'
            ], 422);
        }

        // Fetch the OMDB API
        $response = Http::get(config('services.omdb.url'), [
            'apikey' => config('services.omdb.key'),
            't' => $title,
        ]);

        // Return error if unsuccessful
        if (!$response->successful()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reach OMDB'
            ], 500);
        }

        $data = $response->json();

        if (isset($data['Error'])) {
            return response()->json([
                'status' => 'error',
                'message' => $data['Error']
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'movie' => $data
            ]
        ]);
    }

    public function getMovieById(Request $request, $id) {
        // Fetch the OMDB API
        $response = Http::get(config('services.omdb.url'), [
            'apikey' => config('services.omdb.key'),
            'i' => $id,
        ]);

         $data = $response->json();

        if (isset($data['Error'])) {
            return response()->json([
                'status' => 'error',
                'message' => $data['Error']
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'movie' => $data
            ]
        ]);
    }
}
