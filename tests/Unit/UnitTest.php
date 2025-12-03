<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Role;
use App\Models\Furniture;
use App\Models\Category;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un usuario pertenece a un rol.
     * Verifica la relación belongsTo en el modelo User.
     */
    public function test_usuario_pertenece_a_rol()
    {
        $role = Role::create(['id' => 1, 'name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($role->id, $user->role->id);
    }

    /**
     * Prueba que un rol tiene muchos usuarios.
     * Verifica la relación hasMany en el modelo Role.
     */
    public function test_rol_tiene_muchos_usuarios()
    {
        $role = Role::create(['id' => 3, 'name' => 'Cliente']);
        $user1 = User::factory()->create(['role_id' => $role->id]);
        $user2 = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($role->users->contains($user1));
        $this->assertTrue($role->users->contains($user2));
        $this->assertCount(2, $role->users);
    }

    /**
     * Prueba que un mueble pertenece a una categoría.
     * Verifica la relación belongsTo en el modelo Furniture.
     */
    public function test_mueble_pertenece_a_categoria()
    {
        $category = Category::factory()->create();
        $furniture = Furniture::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $furniture->category);
        $this->assertEquals($category->id, $furniture->category->id);
    }

    /**
     * Prueba que una categoría tiene muchos muebles.
     * Verifica la relación hasMany en el modelo Category.
     */
    public function test_categoria_tiene_muchos_muebles()
    {
        $category = Category::factory()->create();
        $furniture1 = Furniture::factory()->create(['category_id' => $category->id]);
        $furniture2 = Furniture::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->furniture->contains($furniture1));
        $this->assertTrue($category->furniture->contains($furniture2));
        $this->assertCount(2, $category->furniture);
    }
}
