<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;

class PreferenciasController extends Controller
{
    /**
     * Muestra la vista del formulario de preferencias.
     */
    public function show(Request $request)
    {
        // 1. Obtener el ID de la sesión
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');

        // 2. Obtener el usuario activo
        $user = User::activeUserSesion($sesionId);

        // Si no hay usuario, redirigir al login (seguridad básica)
        if (!$user) {
            return redirect()->route('login.show')->withErrors(['error' => 'Debes iniciar sesión para ver tus preferencias.']);
        }

        // 3. Cargar las preferencias actuales de la cookie
        $cookieName = 'preferencias_' . $user->id;
        $cookieValue = $request->cookie($cookieName);

        $preferencias = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        // Si existe la cookie, fusionamos los datos
        if ($cookieValue) {
            // Intentamos decodificar. Si falla, usamos array vacío.
            $decoded = json_decode($cookieValue, true);
            if (is_array($decoded)) {
                $preferencias = array_merge($preferencias, $decoded);
            }
        }

        // 4. Pasar todo a la vista
        return view('preferencias', compact('preferencias', 'sesionId', 'user'));
    }

    /**
     * Almacena las preferencias seleccionadas en el formulario.
     */
    public function update(Request $request)
    {
        // 1. Obtener el ID de la sesión (del formulario POST)
        $sesionId = $request->input('sesionId');

        // 2. Obtener el usuario
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['error' => 'Sesión expirada.']);
        }

        // 3. Validar
        $validatedData = $request->validate([
            'tema' => 'required|string|in:claro,oscuro',
            'moneda' => 'required|string|in:EUR,USD,GBP',
            'tamaño' => 'required|integer|in:6,12,24,48',
        ]);

        // 4. Crear la cookie
        $cookieName = 'preferencias_' . $user->id;

        // Obtenemos los datos viejos para no perder info (si hubiera)
        $oldCookieValue = $request->cookie($cookieName);
        $cookieData = $oldCookieValue ? json_decode($oldCookieValue, true) : [];

        if (!is_array($cookieData)) {
            $cookieData = [];
        }

        // Sobrescribimos con los nuevos valores
        $cookieData['tema'] = $validatedData['tema'];
        $cookieData['moneda'] = $validatedData['moneda'];
        $cookieData['tamaño'] = $validatedData['tamaño'];

        // !! CORRECCIÓN: Usamos la sintaxis simple igual que en LoginController !!
        // Esto asegura que secure, httpOnly y path sean consistentes.
        $cookie = Cookie::make(
            $cookieName,
            json_encode($cookieData),
            60 * 24 * 30 // 30 días
        );

        // 5. Redirigir a la principal con la cookie
        return redirect()
            ->route('principal', ['sesionId' => $sesionId])
            ->withCookie($cookie)
            ->with('success', 'Preferencias actualizadas correctamente.');
    }
}
