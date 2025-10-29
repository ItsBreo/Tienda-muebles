<?php

namespace App\Models;

use JsonSerializable;

class Furniture implements JsonSerializable {
    private int $id;
    private int $id_category;
    private string $name;
    private string $description;
    private float $price;
    private int $stock;
    private string $materials;
    private string $dimensions;
    private string $main_color;
    private bool $salient;
    private string $images;

    public function __construct(
        int $id,
        int $category_id,
        string $name,
        string $description,
        float $price,
        int $stock,
        string $materials,
        string $dimensions,
        string $principal_colour,
        bool $destacado,
        string $images
    ) {
        $this->id = $id;
        $this->category_id = $category_id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->materials = $materials;
        $this->dimensions = $dimensions;
        $this->principal_colour = $principal_colour;
        $this->destacado = $destacado;
        $this->images = $images;
    }

    public function jsonSerialize(): array{
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'materials' => $this->materials,
            'dimensions' => $this->dimensions,
            'main_color' => $this->main_color,
            'salient' => $this->salient,
            'images' => $this->images,
        ];
    }

    // Getters públicos
    public function getId(): int{
        return $this->id;
    }

    public function getCategoryId(): int{
        return $this->category_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getDescription(): string{
        return $this->description;
    }

    public function getPrice(): float{
        return $this->price;
    }

    public function getStock(): int{
        return $this->stock;
    }

    public function getMaterials(): string{
        return $this->materials;
    }

    public function getDimensions(): string{
        return $this->dimensions;
    }

    public function getMain_Color(): string{
        return $this->main_color;
    }

    public function getSalient(): bool{
        return $this->salient;
    }

    public function getImages(): string{
        return $this->images;
    }

    // Metodo datos mockup
    public static function furnitureData(): array{
        return[
            new Furniture(1, 1, "Mueble 1", "Descripcion 1", 45, 3, "Madera de roble oscuro", "70cm x 140cm", "marrón oscuro de roble oscuro", true, "imagen.jpg"),
            new Furniture(2, 1, "Mueble 2", "Descripcion 2", 25, 7, "Madera refinada", "70cm x 140cm", "marrón oscuro de roble oscuro", false, "imagen2.jpg"),
            new Furniture(3, 1, "Mueble 3", "Descripcion 3", 79.99, 19, "Madera no refinada", "70cm x 140cm", "marrón oscuro de roble oscuro", false, "imagen3.jpg"),
            new Furniture(4, 2, "Mueble 4", "Descripcion 4", 15, 0, "Madera obtusa", "70cm x 140cm", "marrón oscuro de roble oscuro", true, "imagen4.jpg"),
            new Furniture(5, 3, "Mueble 5", "Descripcion 5", 10.20, 1, "Madera californiana", "45cm x 20cm", "marrón oscuro de roble oscuro", true, "imagen4.jpg"),
            new Furniture(6, 3, "Mueble 6", "Descripcion 6", 75, 6, "Toco Madera", "70cm x 180cm", "marrón oscuro de roble oscuro", false, "imagen5.jpg"),
            new Furniture(7, 4, "Mueble 7", "Descripcion 7", 35.55, 5, "Madera de Saturno", "50cm x 70cm", "marrón oscuro de roble oscuro", true, "imagen6.jpg"),
            new Furniture(8, 4, "Mueble 8", "Descripcion 8", 60, 3, "Madera de roble claro", "60cm x 100cm", "marrón oscuro de roble oscuro", true, "imagen7.jpg"),
            new Furniture(9, 4, "Mueble 9", "Descripcion 9", 69.99, 5, "Madera de Pino", "50cm x 120cm", "marrón oscuro de roble oscuro", false, "imagen8.jpg"),
            new Furniture(10, 4, "Mueble 10", "Descripcion 10", 7.99, 8, "Madera simple", "10cm x 45cm", "marrón oscuro de roble oscuro", false, "imagen9.jpg"),
        ];
    }

    // Buscar por categoria (-- Cambiar --)
    public static function searchByCategory(int $id): ?Furniture{

        foreach (Furniture::furnitureData() as $furniture) {
            if ($furniture->getId() === $id){
                return $id;
            }
        }
        return null;
    }

}

