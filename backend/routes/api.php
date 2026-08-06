<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PriceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/next-control-number', [TicketController::class, 'nextControlNumber']);
Route::post('/tickets', [TicketController::class, 'store']);
Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
Route::get('/current-price', [PriceController::class, 'currentPrice']);
Route::post('/price-history', [PriceController::class, 'store']);
Route::get('/price-history',[PriceController::class,'index']);
Route::get('/price-history/{id}', [PriceController::class, 'show']); 
Route::put('/price-history/{id}',[PriceController::class,'update']);