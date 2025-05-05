<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\DevolucionesController;
use App\Http\Controllers\RenovacionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/*
Route::get('/', function () {
    return view('welcome');
});
Route::get('/home',[HomeController::class,'index'] )
Route::get('/Prestamo',[PrestamoController::class,'index'] );
Route::get('/Devoluciones',[DevolucionesController::class,'index'] );
Route::get('/Renovaciones',[RenovacionController::class,'index'] );
*/

Route::middleware('auth')->group(function () {
    // Home
    Route::get('/home', [HomeController::class, 'index'])->middleware('auth');
    
    // préstamo
    Route::get('/Prestamo', [PrestamoController::class, 'index'])->middleware('auth');
    Route::post('/Prestamo/realizar', [PrestamoController::class, 'realizarPrestamo'])->name('prestamo.realizar');
    
    // Devolucion
    Route::get('/Devoluciones', [DevolucionesController::class, 'index'])->middleware('auth');
    
    // Renovacion
    Route::get('/Renovaciones', [RenovacionController::class, 'index'])->middleware('auth');

});

    // login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

