<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RenovacionController extends Controller
{
    public function index(){
        
        return view("Renovacion/renovacion");
    }
}
