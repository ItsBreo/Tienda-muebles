<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class PrincipalController extends Controller
{

    public function index() {
        return view('principal');
    }
}
