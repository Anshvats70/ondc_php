<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallbackController;

// ONDC Callback Routes (no session middleware)
Route::post('/on_search', [CallbackController::class, 'on_search']);
