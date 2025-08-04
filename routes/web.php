<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityController;

// Route::get('/', function () {
//     return view('welcome');
// });

// routes/web.php
Route::get('/', [ActivityController::class, 'index'])->name('activity.form');
Route::post('/schedule', [ActivityController::class, 'schedule'])->name('activity.schedule');
Route::get('/wilayah-search', [ActivityController::class, 'search'])->name('wilayah.search');


