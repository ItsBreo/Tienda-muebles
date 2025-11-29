<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Furniture;
use App\Models\User;

class Cart extends Model
{
	use HasFactory;

	protected $table = 'carts';

	/**
	 * Los atributos que se pueden asignar masivamente, necesarios para Cart::create()
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'total_price', 'sesion_id'];

	/**
	 * Define la relación de muchos a muchos con Furniture.
	 */
	public function productos()
	{
		return $this->belongsToMany(
			Furniture::class,
			'cart_furniture',
			'cart_id',
			'furniture_id'
		)
		// CORRECCIÓN FINAL: Usamos 'quantity' y 'unit_price' (para coincidir con la migración)
		->withPivot('quantity', 'unit_price')
		->withTimestamps();
	}

	/**
	 * Define la relación de uno a muchos inversa con User.
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
