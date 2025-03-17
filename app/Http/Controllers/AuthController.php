<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('DocumentoId', 'password');

        $usuario = Usuario::where('DocumentoId', $credentials['DocumentoId'])->first();

        if ($usuario && password_verify($credentials['password'], $usuario->password)) {
            // Autenticación exitosa
            Auth::login($usuario);
            return redirect()->intended('/home'); // Redirige al dashboard o a la página que desees
        }

        // Autenticación fallida
        return back()->withErrors(['loginError' => 'Credenciales incorrectas'])->withInput();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}