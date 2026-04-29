<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Clase Categoría
class Category extends Model {

    use HasFactory;

    protected $table = "categories";

    protected $fillable = ['name', 'description'];

    // Relación categoria 1:N productos
    public function furniture(){
        return $this->hasMany(Furniture::class);
    }

}
