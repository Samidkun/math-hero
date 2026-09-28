<?php

use App\Http\Controllers\GameApiController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/play/{session}', [HomeController::class, 'play'])->name('play');
Route::get('/results/{session}', [HomeController::class, 'results'])->name('results');
Route::get('/join', [HomeController::class, 'join'])->name('join');
Route::get('/classroom/{code}', [HomeController::class, 'classroom'])->name('classroom');
Route::get('/report/{code}/{player}', [HomeController::class, 'report'])->name('report');

Route::prefix('api')->group(function (): void {
    Route::post('/players', [GameApiController::class, 'storePlayer']);
    Route::post('/sessions', [GameApiController::class, 'startSession']);
    Route::post('/sessions/{session}/answer', [GameApiController::class, 'answerQuestion']);
    Route::post('/sessions/{session}/finish', [GameApiController::class, 'finishSession']);
    Route::post('/sessions/{session}/abandon', [GameApiController::class, 'abandonSession']);
});
