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

*/


//Inicio
Route::get('/home',[HomeController::class,'index'] );
//Prestamo
Route::get('/Prestamo',[PrestamoController::class,'index'] );
//Devoluciones
Route::get('/Devoluciones',[DevolucionesController::class,'index'] );
//Renovacion
Route::get('/Renovacion',[RenovacionController::class,'index'] );


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    return "Bienvenido al sistema"; // Aquí puedes redirigir al dashboard real
})->middleware('auth')->name('dashboard');
