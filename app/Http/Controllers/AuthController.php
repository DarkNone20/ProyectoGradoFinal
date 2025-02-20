<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'DocumentoId' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Usuario::where('DocumentoId', $credentials['DocumentoId'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            return redirect()->route('dashboard'); // Redirigir al dashboard
        }

        return back()->withErrors(['loginError' => 'Usuario o contraseña incorrectos']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
