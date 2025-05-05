<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DevolucionesController extends Controller
{
    public function index(){

         // Obtener el usuario autenticado
         $usuarioAutenticado = auth()->user();
        
        return view("devolucion/devoluciones",compact('usuarioAutenticado'));
    }
}
