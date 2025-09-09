<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgressController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/stream-progression', [ProgressController::class, 'streamProgression']);

Route::get('/progress-cache', [ProgressController::class, 'showCache']);
