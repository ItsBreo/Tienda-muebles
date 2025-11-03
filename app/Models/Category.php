<?php

namespace App\Models;

use JsonSerializable;
// Clase Categoría
class Category implements JsonSerializable {
    private int $id;
    private string $name;
    private string $description;

    public function __construct(int $id, string $name, string $description) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    // Implementación de JsonSerializable
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    // Getters públicos
    public function getId(): int {
        return $this->id;
    }
    public function getName(): string {
        return $this->name;
    }
    public function getDescription(): string {
        return $this->description;
    }

    // Setters públicos
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    // Método estático para obtener datos de prueba
    public static function getMockData(): array {
        return [
            new Category(1, 'Salón', 'Muebles para el salón.'),
            new Category(2, 'Dormitorio', 'Camas y mesitas.'),
            new Category(3, 'Oficina', 'Sillas y escritorios.'),
            new Category(4, 'Cocina', 'Muebles de cocina.'),
        ];
    }

    // Método estático para buscar una categoría por ID
    public static function findById(int $id): ?Category {
        foreach (self::getMockData() as $cat) {
            if ($cat->getId() === $id) {
                return $cat;
            }
        }
        return null;
    }
}
