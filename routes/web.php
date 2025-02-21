<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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


Route::get('/home', function () {
    return  "Home" ;
});
*/



Route::get('/home', function () {
    return  view('Home');
});

Route::get('/Prestamo', function () {
    return  view('Prestamo');
});

Route::get('/Devoluciones', function () {
    return  view('Devoluciones');
});

Route::get('/renovacion', function () {
    return  view('Renovacion');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    return "Bienvenido al sistema"; // Aquí puedes redirigir al dashboard real
})->middleware('auth')->name('dashboard');
