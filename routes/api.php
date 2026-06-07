<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\PartyController;
use App\Http\Controllers\Api\SharedFileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hello from the laravel WatchParty'
    ]);
});

// --------------
// AUTHENTICATION
// --------------
Route::post('/users/signup', [AuthController::class, 'signup'])->middleware('throttle:10,60');
Route::post('/users/login', [AuthController::class, 'login'])->middleware('throttle:10,60');
Route::post('/users/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// --------------
// USERS
// --------------
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->group(function () {
        // ---- Me ----
        Route::get('me', [UserController::class, 'getMe']);;
        Route::patch('me', [UserController::class, 'updateMe']);   // PATCH /users/me
        Route::delete('me', [UserController::class, 'destroyMe']); // DELETE /users/me

        // ---- FRIENDS ----
        Route::put('me/friends', [UserController::class, 'addFriend']);
        Route::delete('me/friends/{id}', [UserController::class, 'removeFriend']);

        // ---- MOVIES ----
        Route::put('me/movies', [UserController::class, 'addMovie']);
        Route::delete('me/movies/{id}', [UserController::class, 'removeMovie']);

        // ---- PARTIES ----
        Route::get('me/hosted-parties', [UserController::class, 'myHostedParties']);
        Route::get('{user}/hosted-parties', [UserController::class, 'hostedParties']);

        Route::get('me/parties', [UserController::class, 'myParties']);
        Route::get('{user}/parties', [UserController::class, 'userParties'])->middleware('restrictTo:admin');

        // ---- ARCHIVED PARTIES ----
        Route::get('me/archived-parties', [UserController::class, 'myArchivedParties']);
    });

    Route::apiResource('users', UserController::class)->except(['index', 'update', 'destroy']);
    Route::apiResource('users', UserController::class)->only(['index', 'update', 'destroy'])->middleware('restrictTo:admin');
});

// --------------
// MOVIES
// --------------
Route::get('/movies', [MovieController::class, 'getMovie'])->name('movies.getMovie');
Route::get('/movies/{id}', [MovieController::class, 'getMovieById'])->name('movies.getMovieById');

// --------------
// PARTIES
// --------------
Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('parties', PartyController::class)->except(['index']);
    Route::apiResource('parties', PartyController::class)->only(['index'])->middleware('restrictTo:admin');

    Route::patch('parties/{party}/end', [PartyController::class, 'endParty']);

    // ---- PARTICIPANTS ----
    Route::patch('parties/{party}/participants', [PartyController::class, 'addParticipant']);
    Route::delete(
        'parties/{party}/participants/{id}',
        [PartyController::class, 'removeParticipant']
    );

    // ---- MOVIES ----
    Route::patch('parties/{party}/movies', [PartyController::class, 'addMovie']);
    Route::delete(
        'parties/{party}/movies/{id}',
        [PartyController::class, 'removeMovie']
    );

    // ---- USEFUL LINKS ----
    Route::post('parties/{party}/links', [PartyController::class, 'addUsefulLink']);

    // ---- IMPRESSIONS ----
    Route::post('parties/{party}/impressions', [PartyController::class, 'addPartyImpression']);
    Route::post('parties/{party}/movies/impressions', [PartyController::class, 'addMovieImpression']);

    // ---- SHARED FILES ----
    // SECURITY: No membership check — any authenticated user can list, upload,
    // download, or delete files for any party. In production, restrict to members.
    Route::get('parties/{party}/files', [SharedFileController::class, 'index']);
    Route::post('parties/{party}/files', [SharedFileController::class, 'store']);
    Route::get('parties/{party}/files/{file}', [SharedFileController::class, 'show']);
    Route::delete('parties/{party}/files/{file}', [SharedFileController::class, 'destroy']);
});
