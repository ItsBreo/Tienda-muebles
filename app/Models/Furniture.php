<?php

namespace App\Models;

use JsonSerializable;

// Clase Mueble
class Furniture implements JsonSerializable {

    private int $id;
    private int $categoryId;
    private string $name;
    private string $description;
    private float $price;
    private int $stock;
    private string $materials;
    private string $dimensions;
    private string $mainColor;
    private bool $isSalient;
    private array $images;

    public function __construct(
        int $id,
        int $categoryId,
        string $name,
        string $description,
        float $price,
        int $stock,
        string $materials,
        string $dimensions,
        string $mainColor,
        bool $isSalient,
        array $images
    ) {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->materials = $materials;
        $this->dimensions = $dimensions;
        $this->mainColor = $mainColor;
        $this->isSalient = $isSalient;
        $this->images = $images;
    }

    // Implementación de JsonSerializable
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'materials' => $this->materials,
            'dimensions' => $this->dimensions,
            'main_color' => $this->mainColor,
            'is_salient' => $this->isSalient,
            'images' => $this->images,
        ];
    }

    // Getters públicos
    public function getId(): int {
        return $this->id;
    }

    public function getCategoryId(): int {
        return $this->categoryId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getMaterials(): string {
        return $this->materials;
    }

    public function getDimensions(): string {
        return $this->dimensions;
    }

    public function getMainColor(): string {
        return $this->mainColor;
    }

    public function isSalient(): bool {
        return $this->isSalient;
    }

    public function getImages(): array {
        return $this->images;
    }


    // Método helper para obtener la imagen principal
    public function getMainImage(): string {
        return $this->images[0] ?? 'default.jpg'; // Retornamos la primera imagen o una imagen por defecto
    }

    /**
     * Devolvemos un array con los datos de prueba
     */
    public static function getMockData(): array {
        return [
            new Furniture(1, 1, "Mesa de Centro 'Nórdica'", "Mesa de roble con diseño minimalista.", 149.99, 5, "Madera de roble oscuro", "120cm x 60cm x 45cm", "Roble Oscuro", true, ["salon/mesa_nordica_1.png", "salon/mesa_nordica_2.png", "salon/mesa_nordica_3.png"]),
            new Furniture(2, 1, "Sofá 'Confort'", "Sofá de 3 plazas, tela gris.", 399.99, 3, "Tela y madera", "200cm x 90cm x 85cm", "Gris", false, ["salon/sofa_confort_1.png", "salon/sofa_confort_2.png", "salon/sofa_confort_3.png"]),
            new Furniture(3, 1, "Estantería 'Lineal'", "Estantería modular metálica.", 89.50, 10, "Metal", "80cm x 30cm x 180cm", "Negro", false, ["salon/estanteria_lineal_1.png", "salon/estanteria_lineal_2.png", "salon/estanteria_lineal_3.png"]),
            new Furniture(4, 2, "Cama 'Queen'", "Cama con cabecero tapizado.", 299.00, 2, "Madera y tela", "160cm x 200cm", "Beige", true, ["dormitorio/cama_queen_1.png", "dormitorio/cama_queen_2.png", "dormitorio/cama_queen_3.png"]),
            new Furniture(5, 3, "Silla de Oficina 'Ergo'", "Silla ergonómica con ruedas.", 120.00, 15, "Plástico y malla", "60cm x 60cm x 110cm", "Negro", true, ["oficina/silla_ergo_1.png", "oficina/silla_ergo_2.png", "oficina/silla_ergo_3.png"]),
            new Furniture(6, 3, "Escritorio 'Minimal'", "Escritorio de madera clara y metal.", 110.00, 6, "Madera de pino y metal", "140cm x 70cm x 75cm", "Pino Claro", false, ["oficina/escritorio_minimal_1.png", "oficina/escritorio_minimal_2.png", "oficina/escritorio_minimal_3.png"]),
            new Furniture(7, 4, "Armario de Cocina 'Chef'", "Módulo superior con 2 puertas.", 75.50, 8, "Aglomerado", "80cm x 40cm x 60cm", "Blanco", true, ["cocina/armario_chef_1.png", "cocina/armario_chef_2.png", "cocina/armario_chef_3.png"]),
            new Furniture(8, 4, "Isla 'Gourmet'", "Isla de cocina con almacenaje.", 350.00, 3, "Granito y madera", "120cm x 80cm x 90cm", "Blanco", true, ["cocina/isla_gourmet_1.png", "cocina/isla_gourmet_2.png", "cocina/isla_gourmet_3.png"]),
            new Furniture(9, 2, "Mesita de Noche 'Clásica'", "Mesita con 2 cajones.", 60.00, 10, "Madera de pino", "40cm x 30cm x 55cm", "Pino Claro", false, ["dormitorio/mesita_clasica_1.png", "dormitorio/mesita_clasica_2.png", "dormitorio/mesita_clasica_3.png"]),
            new Furniture(10, 1, "Butaca 'Relax'", "Butaca de lectura color mostaza.", 180.00, 4, "Tela", "70cm x 80cm x 95cm", "Mostaza", false, ["salon/butaca_relax_1.png", "salon/butaca_relax_2.png", "salon/butaca_relax_3.png"]),
            new Furniture(11, 2, "Cama 'Nido'", "Cama individual con cajones.", 210.00, 7, "Madera de pino", "90cm x 200cm", "Blanco", false, ["dormitorio/cama_nido_1.png", "dormitorio/cama_nido_2.png", "dormitorio/cama_nido_3.png"]),
            new Furniture(12, 4, "Mesa de Cocina 'Extensible'", "Mesa para 4-6 personas.", 175.00, 3, "Madera y metal", "140cm (ext. 180cm) x 80cm", "Pino Claro", true, ["cocina/mesa_cocina_1.png", "cocina/mesa_cocina_2.png", "cocina/mesa_cocina_3.png"]),
        ];
    }

    /**
     * Buscamos un mueble por ID en los datos de prueba
     */
    public static function findById(int $id): ?Furniture {
        foreach (self::getMockData() as $furniture) {
            if ($furniture->getId() === $id) {
                return $furniture;
            }
        }
        return null;
    }
}
