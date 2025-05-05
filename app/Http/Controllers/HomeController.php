<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {

        // Obtener el usuario autenticado
        $usuarioAutenticado = auth()->user();

        return view("home/home",compact('usuarioAutenticado'));
    }
}
