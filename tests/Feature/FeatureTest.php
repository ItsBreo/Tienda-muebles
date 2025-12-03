<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Furniture;
use App\Models\Category;

class FeatureTest extends TestCase
{
    // Esto crea las tablas y reinicia la BD en cada test
    use RefreshDatabase;

    /**
     * ¡Función Helper Clave!
     * Simula nuestro flujo de login manual y devuelve el sesionId.
     *
     * @param string $role 'user' o 'admin'
     * @return array [string $sesionId, User $user, string $cookieName]
     */
    private function loginAs($role = 'user')
    {
        // Crear Roles si no existen (necesario porque la BD se vacía)
        if (\App\Models\Role::count() === 0) {
            \App\Models\Role::create(['id' => 1, 'name' => 'Admin']);
            \App\Models\Role::create(['id' => 2, 'name' => 'Gestor']);
            \App\Models\Role::create(['id' => 3, 'name' => 'Cliente']);
        }

        // Configurar datos según el rol
        $roleId = ($role === 'admin') ? 1 : 3;
        $email = ($role === 'admin') ? 'admin@correo.com' : 'jose@correo.com';
        $password = '1234';

        // Crea el usuario en la BD usando Factory
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password), // Encriptamos la contraseña
            'role_id' => $roleId,
            'name' => ($role === 'admin') ? 'Admin' : 'Jose',
        ]);

        // Simulamos el post de login
        $response = $this->post(route('login.store'), [
            'email' => $email,
            'password' => $password,
        ]);

        // Capturamos el sesionId de la URL de redirección
        $location = $response->headers->get('Location');
        $sesionId = null;

        // Extraemos sesionId de la URL
        if ($location) {
            parse_str(parse_url($location, PHP_URL_QUERY), $query);
            $sesionId = $query['sesionId'] ?? null;
        }

        // Si falló el login, el test explotará aquí
        if (!$sesionId) {
            throw new \Exception("Login fallido en el helper loginAs. Revisa las credenciales o el controlador.");
        }

        $cookieName = 'preferencias_' . $user->id;

        // Devolvemos los datos necesarios
        return [$sesionId, $user, $cookieName];
    }


    public function test_login_fallido_credenciales_invalidas()
    {
        // Necesitamos crear roles para que no falle ninguna relación interna, aunque falle el login
        \App\Models\Role::create(['id' => 3, 'name' => 'Cliente']);

        $response = $this->post(route('login.store'), [
            'email' => 'usuario@incorrecto.com',
            'password' => 'mala',
        ]);

        // Debe redirigir de vuelta
        $response->assertStatus(302);
        // Debe mostrar un error de autenticación
        $response->assertSessionHasErrors('autenticationError');
    }

    public function test_login_exitoso_redirige_y_crea_sesion_de_usuario()
    {
        // Crear usuario previo
        $role = new \App\Models\Role();
        $role->id = 3;
        $role->name = 'Cliente';
        $role->save();

        User::factory()->create([
            'email' => 'jose@correo.com',
            'password' => Hash::make('1234'),
            'role_id' => 3
        ]);
        $response = $this->post(route('login.store'), [
            'email' => 'jose@correo.com',
            'password' => '1234',
        ]);

        // Debe redirigir (a principal o preferencias)
        $response->assertStatus(302);

        // Comprobamos que el array 'usuarios' en la sesión ahora tiene un miembro
        $this->assertCount(1, Session::get('usuarios'));
    }

    public function test_logout_limpia_sesion_de_usuario_especifico()
    {
        // Iniciamos sesión como 'user'
        [$sesionId, $user] = $this->loginAs('user');
        $this->assertNotNull(Session::get('usuarios')[$sesionId]);

        // Hacemos logout con ese sesionId
        $response = $this->post(route('login.logout'), [
            'sesionId' => $sesionId,
        ]);

        // Comprobamos que ESE usuario ya no está en la sesión
        $response->assertRedirect(route('principal'));
        $this->assertFalse(isset(Session::get('usuarios')[$sesionId]));
    }

    public function test_admin_bloqueado_sin_sesion()
    {
        // Intentamos acceder al index de admin sin sesionId
        $response = $this->get(route('admin.muebles.index'));

        // Debe redirigir al login con un error
        $response->assertRedirect(route('login.show'));
        $response->assertSessionHas('error');
    }

    public function test_admin_bloqueado_con_rol_usuario()
    {
        // Iniciamos sesión como usuario NORMAL
        [$sesionId, $user] = $this->loginAs('user');

        // Intentamos acceder al admin con el sesionId del usuario
        $response = $this->get(route('admin.muebles.index', ['sesionId' => $sesionId]));

        // Debe redirigir a 'principal' con un error de admin
        $response->assertRedirect(route('principal', ['sesionId' => $sesionId]));
        $response->assertSessionHas('error-admin');
    }

    public function test_admin_acceso_exitoso_con_rol_admin()
    {
        // Iniciamos sesión como ADMIN
        [$sesionId, $user] = $this->loginAs('admin');

        // Intentamos acceder al admin con el sesionId del admin
        $response = $this->get(route('admin.muebles.index', ['sesionId' => $sesionId]));

        // Debe cargar la vista correctamente (OK 200)
        $response->assertStatus(200);
        $response->assertViewIs('admin.muebles.index');
    }

    // =================================================================
    // Pruebas de Cookies y Preferencias (Requisito 1)
    // =================================================================

    public function test_preferencias_cambia_tema_y_moneda()
    {
        // Iniciamos sesión
        [$sesionId, $user, $cookieName] = $this->loginAs('user');

        // Enviamos el formulario de preferencias
        $response = $this->post(route('preferencias.update'), [
            'sesionId' => $sesionId,
            'tema' => 'oscuro',
            'moneda' => 'USD',
            'tamaño' => 12,
        ]);

        // Comprobamos la redirección
        $response->assertRedirect(route('principal', ['sesionId' => $sesionId]));

        // Comprobamos que la respuesta LLEVA la cookie actualizada
        $response->assertCookie($cookieName);
        $cookie = $response->getCookie($cookieName);
        $this->assertStringContainsString('"tema":"oscuro"', $cookie->getValue());
        $this->assertStringContainsString('"moneda":"USD"', $cookie->getValue());
    }

    public function test_preferencias_paginacion_se_aplica_al_catalogo()
    {
        // Iniciamos sesión
        [$sesionId, $user, $cookieName] = $this->loginAs('user');

        // Crear muebles para paginar
        Category::factory()->create();
        Furniture::factory()->count(15)->create();

        // Creamos una cookie de preferencia con 12 por página
        $preferencias = json_encode(['tamaño' => 12]);

        // Hacemos la petición al catálogo, pasando el sesionId y la cookie
        $response = $this->withCookie($cookieName, $preferencias)
                         ->get(route('muebles.index', ['sesionId' => $sesionId]));

        // Comprobamos que el paginador se configuró a 12
        $response->assertStatus(200);
        $paginator = $response->viewData('muebles'); // Obtenemos la variable $muebles
        $this->assertEquals(12, $paginator->perPage());
    }


    public function test_catalogo_filtra_por_categoria()
    {
        [$sesionId, $user] = $this->loginAs('user');

        // Crear datos
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();
        Furniture::factory()->create(['category_id' => $cat1->id]);
        Furniture::factory()->create(['category_id' => $cat2->id]);

        // Pedimos la categoría 1
        $response = $this->get(route('muebles.index', [
            'sesionId' => $sesionId,
            'category' => $cat1->id
        ]));

        $response->assertStatus(200);
        // Comprobamos que todos los muebles en la vista son de esa categoría
        $muebles = $response->viewData('muebles');
        foreach ($muebles as $mueble) {
            $this->assertEquals($cat1->id, $mueble->category_id);
        }
    }

    public function test_catalogo_ordena_por_precio_asc()
    {
        [$sesionId, $user] = $this->loginAs('user');

        Category::factory()->create();
        // Crear muebles con precios conocidos
        Furniture::factory()->create(['price' => 100]);
        Furniture::factory()->create(['price' => 50]);
        Furniture::factory()->create(['price' => 200]);
        // Pedimos ordenar por precio ascendente
        $response = $this->get(route('muebles.index', [
            'sesionId' => $sesionId,
            'sort' => 'price_asc'
        ]));

        $response->assertStatus(200);

        // Comprobamos que los precios están en orden
        $muebles = $response->viewData('muebles')->items();
        $this->assertTrue($muebles[0]->price <= $muebles[1]->price);
        $this->assertTrue($muebles[1]->price <= $muebles[2]->price);
    }


    public function test_carrito_anadir_producto()
    {
        [$sesionId, $user] = $this->loginAs('user');
        $cartKey = 'carrito_' . $user->id;

        // Necesitamos un mueble real en BD
        Category::factory()->create();
        $mueble = Furniture::factory()->create(['stock' => 10]); // Aseguramos que haya stock

        // Hacemos POST para añadir el mueble
        $response = $this->post(route('carrito.add', ['mueble' => $mueble->id]), [
            'sesionId' => $sesionId,
            'quantity' => 1,
        ]);

        // Comprobamos la redirección y que la sesión del carrito tiene el item
        $response->assertRedirect(route('carrito.show', ['sesionId' => $sesionId]));
        $this->assertNotNull(Session::get($cartKey)[$mueble->id]);
        $this->assertEquals(1, Session::get($cartKey)[$mueble->id]['cantidad']);
    }

    public function test_carrito_eliminar_item()
    {
        [$sesionId, $user] = $this->loginAs('user');
        $cartKey = 'carrito_' . $user->id;

        // "Sembramos" el carrito con un item
        Session::put($cartKey, [
            3 => ['id' => 3, 'nombre' => 'Test', 'precio' => 100, 'cantidad' => 1]
        ]);
        $this->assertCount(1, Session::get($cartKey));

        // Hacemos DELETE (vía POST con _method) para eliminar el mueble 3
        $response = $this->post(route('carrito.remove', ['mueble' => 3]), [
            'sesionId' => $sesionId,
        ]);

        // Comprobamos que el carrito está vacío
        $response->assertRedirect(route('carrito.show', ['sesionId' => $sesionId]));
        $this->assertCount(0, Session::get($cartKey));
    }


    public function test_admin_puede_crear_un_mueble()
    {
        [$sesionId, $user] = $this->loginAs('admin');

        // Crear una categoría para que la validación no falle
        $categoria = Category::factory()->create();

        // Hacemos POST para crear un nuevo mueble (esto usa la sesión, no la BD)
        $response = $this->post(route('admin.muebles.store'), [
            'sesionId' => $sesionId,
            'name' => 'Mueble de Prueba',
            'category_id' => $categoria->id,
            'description' => 'Desc',
            'price' => 99,
            'stock' => 10,
            'main_color' => 'Rojo',
        ]);

        // Comprobamos que redirige al index y que el mueble existe en la BD
        $response->assertRedirect(route('admin.muebles.index'));
        $this->assertDatabaseHas('furniture', [
            'name' => 'Mueble de Prueba',
            'price' => 99
        ]);
    }

    public function test_admin_puede_eliminar_un_mueble()
    {
        [$sesionId, $user] = $this->loginAs('admin');

        // Crear una categoría y un mueble para poder borrarlo
        Category::factory()->create();
        $mueble = Furniture::factory()->create();
        $this->assertDatabaseHas('furniture', ['id' => $mueble->id]);

        // Hacemos DELETE para eliminar el mueble
        $response = $this->delete(route('admin.muebles.destroy', ['mueble' => $mueble->id]), [
            'sesionId' => $sesionId // Añadimos el sesionId para el middleware checkAdmin
        ]);

        // Comprobamos que redirige y que el mueble ya no está en la BD
        $response->assertRedirect(route('admin.muebles.index')); // La ruta de redirección no lleva sesionId
        $this->assertDatabaseMissing('furniture', ['id' => $mueble->id]);
    }

    public function test_admin_puede_ver_formulario_edicion_mueble()
    {
        // Login como Admin
        [$sesionId, $user] = $this->loginAs('admin');

        // Creamos datos previos (Categoría y Mueble)
        $category = Category::factory()->create();
        $furniture = Furniture::factory()->create(['category_id' => $category->id]);

        // GET a la ruta de edición pasando el sesionId (requerido por checkAdmin)
        $response = $this->get(route('admin.muebles.edit', [
            'mueble' => $furniture->id,
            'sesionId' => $sesionId
        ]));

        // Aserciones
        $response->assertStatus(200);
        $response->assertViewIs('admin.muebles.edit');
        // Verificamos que la vista cargó el mueble correcto
        $response->assertViewHas('mueble', function($viewMueble) use ($furniture) {
            return $viewMueble->id === $furniture->id;
        });
    }

    public function test_admin_puede_actualizar_un_mueble()
    {
        // Login como Admin
        [$sesionId, $user] = $this->loginAs('admin');

        // Creamos datos previos
        $category = Category::factory()->create();
        $furniture = Furniture::factory()->create(['category_id' => $category->id]);

        // Datos nuevos para actualizar (incluyendo sesionId para el middleware manual)
        $updatedData = [
            'sesionId' => $sesionId,
            'name' => 'Nombre Actualizado',
            'description' => 'Nueva descripción del producto',
            'price' => 199.99,
            'stock' => 20,
            'category_id' => $category->id,
            'main_color' => 'Verde',
            'is_salient' => 'on' // Simula checkbox activado
        ];

        // PUT a la ruta update
        $response = $this->put(route('admin.muebles.update', ['mueble' => $furniture->id]), $updatedData);

        // Aserciones
        $response->assertRedirect(route('admin.muebles.index'));

        // Verificamos que en la BD hayan cambiado los valores
        $this->assertDatabaseHas('furniture', [
            'id' => $furniture->id,
            'name' => 'Nombre Actualizado',
            'price' => 199.99,
            'main_color' => 'Verde'
        ]);
    }
}
