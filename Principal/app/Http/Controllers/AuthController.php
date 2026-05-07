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
            $data = $response->json();

            Session::put('token', $data['token']);
            Session::put('user', $data['user']);

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
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $response = Http::post("{$this->apiUsersUrl}/api/register", [
            'name'                  => $request->name,
            'email'                 => $request->email,
            'password'              => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            Session::put('token', $data['token']);
            Session::put('user', $data['user']);

            return redirect()->route('principal');
        }

        $errors = $response->json('errors', ['general' => $response->json('message', 'Error al registrarse.')]);
        return back()->withErrors($errors)->withInput();
    }

    // ── LOGOUT ────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $token = Session::get('token');

        if ($token) {
            Http::withToken($token)->post("{$this->apiUsersUrl}/api/logout");
        }

        Session::forget(['token', 'user']);

        return redirect()->route('principal');
    }
}
