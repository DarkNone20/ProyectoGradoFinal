<?php

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\DevolucionesController;
use App\Http\Controllers\RenovacionController;

// Rutas públicas
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'apiRegister']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiLogout']);

    // Préstamos
    Route::get('/prestamos', [PrestamoController::class, 'apiIndex']);
    Route::post('/prestamos', [PrestamoController::class, 'apiStore']);
    Route::get('/prestamos/{id}', [PrestamoController::class, 'apiShow']);
    Route::put('/prestamos/{id}', [PrestamoController::class, 'apiUpdate']);
 

    // Devoluciones
    Route::get('/devoluciones', [DevolucionesController::class, 'apiIndex']);
    Route::post('/devoluciones', [DevolucionesController::class, 'apiStore']);
    Route::get('/devoluciones/{id}', [DevolucionesController::class, 'apiShow']);
    Route::put('/devoluciones/{id}', [DevolucionesController::class, 'apiUpdate']);
    

   
});