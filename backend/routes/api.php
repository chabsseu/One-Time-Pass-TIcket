<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/tickets',[TicketController::class,'store']);
Route::get('/tickets/next-control-number', [TicketController::class, 'nextControlNumber']);