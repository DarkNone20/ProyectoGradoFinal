<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'DocumentoId' => 'required|string',
            'password' => 'required|string'
        ]);

        $usuario = Usuario::where('DocumentoId', $request->DocumentoId)->first();

        // Respuesta para API
        if ($request->is('api/*')) {
            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                return response()->json([
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            // Crear token de acceso
            $token = $usuario->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }

        // Respuesta para web tradicional
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->withErrors([
                'loginError' => 'Credenciales incorrectas'
            ])->withInput();
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        // Para API
        if ($request->is('api/*')) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Sesión cerrada']);
        }

        // Para web
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}