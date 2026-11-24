<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProgramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('jwt');

Route::apiResource('programs', ProgramController::class)
    ->middleware('jwt');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('jwt');
