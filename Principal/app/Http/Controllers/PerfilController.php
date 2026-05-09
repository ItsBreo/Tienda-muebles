<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PerfilController extends Controller
{
    public function show(Request $request)
    {
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        $user = Session::get('user');

        if (!$user) {
            return redirect()->route('login.show')->withErrors([
                'error' => 'Debes iniciar sesión para ver tu perfil.',
            ]);
        }

        return view('perfil.show', compact('user', 'sesionId'));
    }
}
