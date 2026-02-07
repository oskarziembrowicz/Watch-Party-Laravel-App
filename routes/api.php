<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\PartyController;
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
Route::post('/users/signup', [AuthController::class, 'signup']);
Route::post('/users/login', [AuthController::class, 'login']);
Route::get('/users/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// --------------
// USERS
// --------------
Route::get('/users/me', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('users', UserController::class);

// --------------
// MOVIES
// --------------
Route::get('/movies', [MovieController::class, 'getMovie'])->name('movies.getMovie');
Route::get('/movies/{id}', [MovieController::class, 'getMovieById'])->name('movies.getMovieById');

// --------------
// PARTIES
// --------------
Route::apiResource('parties', PartyController::class);
// Route::get('parties', [PartyController::class,'list'])->name('parties.list');
// Route::post('/parties', [PartyController::class, 'store'])->name('parties.store');

// Route: GET /api/parties/{id}
// Desc: Get party by id
// Route::get('/parties/{party}', [PartyController::class, 'access'])->name('parties.access');

// Route: PATCH /api/parties/{id}
// Desc: Update party
// Route::patch('/parties/{id}', [PartyController::class, 'update']);
