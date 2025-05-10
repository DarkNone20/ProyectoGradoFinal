<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\DevolucionesController;
use App\Http\Controllers\RenovacionController;

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/Prestamo', [PrestamoController::class, 'index']);
    Route::post('/Prestamo/realizar', [PrestamoController::class, 'realizarPrestamo'])->name('prestamo.realizar');
    Route::middleware('auth')->group(function () {
        // ... otras rutas ...
        Route::get('/Devoluciones', [DevolucionesController::class, 'index']);
        Route::post('/Devoluciones/procesar', [DevolucionesController::class, 'procesarDevolucion'])->name('devolucion.procesar');
    });
    Route::get('/Renovaciones', [RenovacionController::class, 'index']);
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');