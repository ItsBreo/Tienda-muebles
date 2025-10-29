<?php

class Category{
    private int $id;
    private string $name;
    private string $description;

    public function __construct(
        int $id,
        string $name,
        string $description
    ){
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public static function categoryData(): array{
        new Category(1, "Categoria Mueble con id: 1", "Descripcion 1");
        new Category(2, "Categoria Mueble con id: 2", "Descripcion 2");
        new Category(3, "Categoria Mueble con id: 3", "Descripcion 3");
        new Category(4, "Categoria Mueble con id: 4", "Descripcion 4");
    }
}

?>
