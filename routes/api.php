<?php

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\DevolucionesController;
//use App\Http\Controllers\RenovacionController;

// Rutas públicas


//Route::post('/login', [AuthController::class, 'apiLogin']);
//Route::post('/login', [AuthController::class, 'apiLoginToken']);
Route::post('/register', [AuthController::class, 'apiRegister']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiLogout']);

    // Préstamos
    //Route::get('/prestamos', [PrestamoController::class, 'apiIndex']);
    Route::post('/prestamos', [PrestamoController::class, 'apiStore']);
    Route::get('/prestamos/{id}', [PrestamoController::class, 'apiShow']);
    Route::put('/prestamos/{id}', [PrestamoController::class, 'apiUpdate']);
    Route::get('/accion-cajon', [PrestamoController::class, 'accionCajon']); // ESP consulta si debe abrir
    Route::post('/accion-cajon-realizada', [PrestamoController::class, 'accionCajonRealizada']); // ESP avisa que abrió



    // Devoluciones
    Route::get('/devoluciones', [DevolucionesController::class, 'apiIndex']);
    Route::post('/devoluciones', [DevolucionesController::class, 'apiStore']);
    Route::get('/devoluciones/{id}', [DevolucionesController::class, 'apiShow']);
    Route::put('/devoluciones/{id}', [DevolucionesController::class, 'apiUpdate']);
    // Para que el ESP8266 consulte si debe abrir el cajón para devolución
    Route::get('/accion-cajon-devolucion', [PrestamoController::class, 'accionCajonDevolucion']);
    // Para que el ESP8266 confirme que ya abrió el cajón para devolución
    Route::post('/accion-cajon-devolucion-realizada', [PrestamoController::class, 'accionCajonDevolucionRealizada']);
});
