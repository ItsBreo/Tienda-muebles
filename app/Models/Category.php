<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Clase Categoría
class Category extends Model {

    use HasFactory;

    protected $table = "categories";

    protected $fillable = ['id', 'nombre'];

    // Relación categoria 1:N productos
    public function furniture(){
        return $this->hasMany(Furniture::class);
    }

    /**
     * Devolvemos un array con los datos de prueba
     */
    public static function getMockData(): array {
        return [
            new Category(['id' => 1, 'nombre' => 'Salón']),
            new Category(['id' => 2, 'nombre' => 'Dormitorio']),
            new Category(['id' => 3, 'nombre' => 'Oficina']),
            new Category(['id' => 4, 'nombre' => 'Cocina']),
        ];
    }

    /**
     * Buscamos una categoría por ID en los datos de prueba
     */
    public static function findById(int $id): ?Category {
        $category = null;
        foreach (self::getMockData() as $cat) {
            if ($cat->id === $id) {
                $category = $cat;
                break;
            }
        }
        return $category;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->nombre;
    }

    public function setName(string $name): void {
        $this->nombre = $name;
    }

    public function getDescription(): ?string {
        return $this->descripcion;
    }

    public function setDescription(string $description): void {
        $this->descripcion = $description;
    }

}
