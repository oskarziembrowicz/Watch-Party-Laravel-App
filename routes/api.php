<?php

use App\Http\Controllers\Api\MovieController;
use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hello from the laravel WatchParty'
        ]);
});

Route::get('/movies', [MovieController::class,'getMovie'])->name('movies.getMovie');

Route::get('/movies/{id}', [MovieController::class,'getMovieById'])->name('movies.getMovieById');
