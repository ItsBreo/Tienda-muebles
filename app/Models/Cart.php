<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total_price'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_productos','carrito_id', 'producto_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
