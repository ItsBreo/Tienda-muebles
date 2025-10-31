<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Category;
use App\Models\Furniture;

// Controlador Principal
class PrincipalController extends Controller{
    // Página principal
    public function index() {
        $categories = Category::getMockData();
        // Muebles destacados (salient)
        $featured = collect(Furniture::getMockData())->filter(fn($featured) => $featured->isSalient())->take(6)->values();

        // Vista principal (hace compact con categorías y destacados)
        return view('principal', compact('categories', 'featured'));
    }
}
