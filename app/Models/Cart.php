<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_productos','carrito_id', 'producto_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
