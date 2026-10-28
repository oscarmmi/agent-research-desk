<?php

use App\Http\Controllers\ResearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ResearchController::class, 'index'])->name('home');
Route::post('/research', [ResearchController::class, 'store'])->name('research.store');
