<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ========== MÉTODOS API ==========
    public function apiRegister(Request $request)
    {
        $request->validate([
            'DocumentoId' => 'required|unique:Usuarios,DocumentoId',
            'Nombre'      => 'required|string|max:255',
            'Apellido'    => 'required|string|max:255',
            'Direccion'   => 'nullable|string|max:255',
            'Telefono'    => 'nullable|string|max:50',
            'Email'       => 'required|email|unique:Usuarios,Email',
            'password'    => 'required|min:6',
        ]);

        $usuario = Usuario::create([
            'DocumentoId' => $request->DocumentoId,
            'Nombre'      => $request->Nombre,
            'Apellido'    => $request->Apellido,
            'Direccion'   => $request->Direccion,
            'Telefono'    => $request->Telefono,
            'Email'       => $request->Email,
            'password'    => bcrypt($request->password),
        ]);

        $token = $usuario->createToken('API Token')->plainTextToken;

        return response()->json(['user' => $usuario, 'token' => $token], 201);
        
    }
    public function apiLogin(Request $request)
    {
        $usuario = \App\Models\Usuario::where('DocumentoId', $request->DocumentoId)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Esto autentica al usuario y crea la sesión
        Auth::login($usuario);
        $request->session()->regenerate();

        return response()->json(['message' => 'Login exitoso']);
    }


    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout exitoso']);
    }

    // ========== MÉTODOS WEB (formulario tradicional) ==========

    // Muestra la vista del formulario de login web
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Procesa el login web tradicional
    public function login(Request $request)
    {
        $credentials = $request->only('DocumentoId', 'password');
        if (Auth::attempt(['DocumentoId' => $credentials['DocumentoId'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/home');
        }
        return back()->withErrors([
            'DocumentoId' => 'Las credenciales no coinciden.',
        ]);
    }

    // Logout web tradicional
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
