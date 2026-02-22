<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketController; // importo el controlador

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Creo la ruta para la API de tickets (esta ruta crea GET, POST, PUT y DELETE automáticamente)
Route::apiResource('tickets', TicketController::class);


