<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    private string $apiUsersUrl;

    public function __construct()
    {
        $this->apiUsersUrl = env('API_USERS_URL', 'http://127.0.0.1:8001');
    }

    // ── LOGIN ─────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (Session::has('user')) {
            return redirect()->route('principal');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $response = Http::post("{$this->apiUsersUrl}/api/login", [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            // La API devuelve: { success, message, data: { token, token_type, abilities, session_id, user } }
            $data = $response->json('data');

            Session::put('token',      $data['token']);
            Session::put('session_id', $data['session_id']);
            Session::put('user',       $data['user']);

            // Si es Admin, redirigir directamente al panel de administración
            if (($data['user']['rol_name'] ?? '') === 'Admin') {
                return redirect()->route('admin.muebles.index');
            }

            return redirect()->route('principal');
        }

        return back()->withErrors([
            'errorCredenciales' => $response->json('message', 'Credenciales incorrectas.'),
        ])->withInput();
    }

    // ── REGISTRO ──────────────────────────────────────────────────────────────

    public function showRegister()
    {
        if (Session::has('user')) {
            return redirect()->route('principal');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:30',
            'surname'               => 'required|string|max:30',
            'email'                 => 'required|email|max:255',
            'password'              => 'required|string|min:4|confirmed',
        ]);

        // 1. Registrar el usuario en la API
        $registerResponse = Http::post("{$this->apiUsersUrl}/api/register", [
            'name'                  => $request->name,
            'surname'               => $request->surname,
            'email'                 => $request->email,
            'password'              => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        if (!$registerResponse->successful()) {
            $errors = $registerResponse->json('errors', [
                'general' => $registerResponse->json('message', 'Error al registrarse.'),
            ]);
            return back()->withErrors($errors)->withInput();
        }

        // 2. La API de registro no devuelve token — hacemos login automático
        $loginResponse = Http::post("{$this->apiUsersUrl}/api/login", [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        if ($loginResponse->successful()) {
            $data = $loginResponse->json('data');

            Session::put('token',      $data['token']);
            Session::put('session_id', $data['session_id']);
            Session::put('user',       $data['user']);

            return redirect()->route('principal');
        }

        // Registro OK pero login falló — redirigir al login manualmente
        return redirect()->route('login.show')
            ->with('success', 'Cuenta creada correctamente. Por favor, inicia sesión.');
    }

    // ── LOGOUT ────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $token     = Session::get('token');
        $sessionId = Session::get('session_id');

        if ($token) {
            Http::withToken($token)->post("{$this->apiUsersUrl}/api/logout", [
                'session_id' => $sessionId,
            ]);
        }

        Session::forget(['token', 'session_id', 'user']);

        return redirect()->route('principal');
    }
}
