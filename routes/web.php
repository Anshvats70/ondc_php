<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CallbackController;

Route::get('/', function () {
    return view('welcome');
});

// ONDC Callback Routes - Clean controller approach
Route::post('/bapl/on_search', [CallbackController::class, 'on_search'])->withoutMiddleware(['web']);
Route::post('/bapl/on_select', [CallbackController::class, 'on_select'])->withoutMiddleware(['web']);
Route::post('/bapl/on_init', [CallbackController::class, 'on_init'])->withoutMiddleware(['web']);
Route::post('/bapl/on_update', [CallbackController::class, 'on_update'])->withoutMiddleware(['web']);
Route::post('/bapl/on_cancel', [CallbackController::class, 'on_cancel'])->withoutMiddleware(['web']);
Route::post('/bapl/on_track', [CallbackController::class, 'on_track'])->withoutMiddleware(['web']);
Route::post('/bapl/on_status', [CallbackController::class, 'on_status'])->withoutMiddleware(['web']);
