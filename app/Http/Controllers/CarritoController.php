<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mueble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $request->query('sesionId');

        $usuario = User
    }
}