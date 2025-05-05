<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RenovacionController extends Controller
{
    public function index(){
        
        // Obtener el usuario autenticado
        $usuarioAutenticado = auth()->user();
        
        return view("Renovacion/renovacion",compact('usuarioAutenticado'));
    }
}
