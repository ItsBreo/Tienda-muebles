<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Furniture;
use App\Models\Category;

class Test extends TestCase
{
    // ¡IMPORTANTE! Esto crea las tablas y reinicia la BD en cada test
    use RefreshDatabase;

    /**
     * ¡Función Helper Clave!
     * Crea un usuario y simula nuestro flujo de login manual.
     *
     * @param string $role 'user' o 'admin'
     * @return array [string $sesionId, User $user, string $cookieName]
     */
    private function loginAs($role = 'user')
    {
        // 0. Crear Roles si no existen (necesario porque la BD se vacía)
        if (Role::count() === 0) {
            Role::create(['id' => 1, 'name' => 'Admin']);
            Role::create(['id' => 2, 'name' => 'Gestor']);
            Role::create(['id' => 3, 'name' => 'Cliente']);
        }

        // 1. Configurar datos según el rol
        $roleId = ($role === 'admin') ? 1 : 3;
        $email = ($role === 'admin') ? 'admin@correo.com' : 'jose@correo.com';
        $password = '1234';

        // 2. CREAR el usuario en la BD usando Factory
        // Usamos factory() en lugar de verifyUser() que no existe
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password), // Encriptamos la contraseña
            'role_id' => $roleId,
            'name' => ($role === 'admin') ? 'Admin' : 'Jose',
        ]);

        // 3. Simulamos el post de login
        $response = $this->post(route('login.store'), [
            'email' => $email,
            'password' => $password,
        ]);

        // 4. Capturamos el sesionId de la URL de redirección
        $location = $response->headers->get('Location');
        $sesionId = null;

        // Extraemos sesionId de la URL
        if ($location) {
            parse_str(parse_url($location, PHP_URL_QUERY), $query);
            $sesionId = $query['sesionId'] ?? null;
        }

        // Si falló el login, el test explotará aquí, lo cual es bueno para depurar
        if (!$sesionId) {
            throw new \Exception("Login fallido en el helper loginAs. Revisa las credenciales o el controlador.");
        }

        $cookieName = 'preferencias_' . $user->id;

        // 5. Devolvemos los datos necesarios
        return [$sesionId, $user, $cookieName];
    }

    // =================================================================
    // Pruebas de Login, Logout y Seguridad
    // =================================================================

    public function test_login_fallido_credenciales_invalidas()
    {
        // Necesitamos crear roles para que no falle ninguna relación interna, aunque falle el login
        Role::create(['id' => 3, 'name' => 'Cliente']);

        $response = $this->post(route('login.store'), [
            'email' => 'usuario@incorrecto.com',
            'password' => 'mala',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('autenticationError');
    }

    public function test_login_exitoso_redirige_y_crea_sesion_de_usuario()
    {
        // 1. Crear usuario previo (Arrange)
        Role::create(['id' => 3, 'name' => 'Cliente']);
        User::factory()->create([
            'email' => 'jose@correo.com',
            'password' => Hash::make('1234'),
            'role_id' => 3
        ]);

        // 2. Actuar
        $response = $this->post(route('login.store'), [
            'email' => 'jose@correo.com',
            'password' => '1234',
        ]);

        // 3. Verificar
        $response->assertStatus(302);
        $this->assertCount(1, Session::get('usuarios'));
    }

    public function test_logout_limpia_sesion_de_usuario_especifico()
    {
        [$sesionId, $user] = $this->loginAs('user');
        $this->assertNotNull(Session::get('usuarios')[$sesionId]);

        $response = $this->post(route('login.logout'), [
            'sesionId' => $sesionId,
        ]);

        $response->assertRedirect(route('principal'));
        // Verificamos que ya NO está en el array
        $this->assertFalse(isset(Session::get('usuarios')[$sesionId]));
    }

    public function test_admin_bloqueado_sin_sesion()
    {
        $response = $this->get(route('admin.muebles.index'));
        $response->assertRedirect(route('login.show'));
        $response->assertSessionHas('error');
    }

    public function test_admin_bloqueado_con_rol_usuario()
    {
        [$sesionId, $user] = $this->loginAs('user');

        $response = $this->get(route('admin.muebles.index', ['sesionId' => $sesionId]));

        $response->assertRedirect(route('principal', ['sesionId' => $sesionId]));
        $response->assertSessionHas('error-admin');
    }

    public function test_admin_acceso_exitoso_con_rol_admin()
    {
        [$sesionId, $user] = $this->loginAs('admin');

        $response = $this->get(route('admin.muebles.index', ['sesionId' => $sesionId]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.muebles.index');
    }

    // =================================================================
    // Pruebas de Cookies y Preferencias
    // =================================================================

    public function test_preferencias_cambia_tema_y_moneda()
    {
        [$sesionId, $user, $cookieName] = $this->loginAs('user');

        $response = $this->post(route('preferencias.update'), [
            'sesionId' => $sesionId,
            'tema' => 'oscuro',
            'moneda' => 'USD',
            'tamaño' => 12,
        ]);

        $response->assertRedirect(route('principal', ['sesionId' => $sesionId]));

        $response->assertCookie($cookieName);
        $cookie = $response->getCookie($cookieName);
        $this->assertStringContainsString('"tema":"oscuro"', $cookie->getValue());
        $this->assertStringContainsString('"moneda":"USD"', $cookie->getValue());
    }

    public function test_preferencias_paginacion_se_aplica_al_catalogo()
    {
        [$sesionId, $user, $cookieName] = $this->loginAs('user');

        // Crear muebles para paginar
        Category::factory()->create();
        Furniture::factory()->count(15)->create();

        $preferencias = json_encode(['tamaño' => 12]);

        $response = $this->withCookie($cookieName, $preferencias)
                         ->get(route('muebles.index', ['sesionId' => $sesionId]));

        $response->assertStatus(200);
        $paginator = $response->viewData('muebles');
        $this->assertEquals(12, $paginator->perPage());
    }

    // =================================================================
    // Pruebas de Catálogo
    // =================================================================

    public function test_catalogo_filtra_por_categoria()
    {
        [$sesionId, $user] = $this->loginAs('user');

        // Crear datos
        $cat1 = Category::factory()->create(['id' => 1]);
        $cat2 = Category::factory()->create(['id' => 2]);
        Furniture::factory()->create(['category_id' => 1]);
        Furniture::factory()->create(['category_id' => 2]);

        $response = $this->get(route('muebles.index', [
            'sesionId' => $sesionId,
            'category' => 1
        ]));

        $response->assertStatus(200);
        $muebles = $response->viewData('muebles');

        foreach ($muebles as $mueble) {
            $this->assertEquals(1, $mueble->category_id);
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

        $response = $this->get(route('muebles.index', [
            'sesionId' => $sesionId,
            'sort' => 'price_asc'
        ]));

        $response->assertStatus(200);
        $muebles = $response->viewData('muebles')->items();

        // 50 <= 100 <= 200
        $this->assertTrue($muebles[0]->price <= $muebles[1]->price);
        $this->assertTrue($muebles[1]->price <= $muebles[2]->price);
    }

    // =================================================================
    // Pruebas de Carrito
    // =================================================================

    public function test_carrito_anadir_producto()
    {
        [$sesionId, $user] = $this->loginAs('user');
        $cartKey = 'carrito_' . $user->id; // Corregido getId() -> id

        // Necesitamos un mueble real en BD
        Category::factory()->create();
        $mueble = Furniture::factory()->create(['id' => 3]);

        $response = $this->post(route('carrito.add', ['mueble' => 3]), [
            'sesionId' => $sesionId,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('carrito.show', ['sesionId' => $sesionId]));
        $this->assertNotNull(Session::get($cartKey)[3]);
        $this->assertEquals(1, Session::get($cartKey)[3]['cantidad']);
    }

    public function test_carrito_eliminar_item()
    {
        [$sesionId, $user] = $this->loginAs('user');
        $cartKey = 'carrito_' . $user->id;

        Session::put($cartKey, [
            3 => ['id' => 3, 'nombre' => 'Test', 'precio' => 100, 'cantidad' => 1]
        ]);

        $response = $this->post(route('carrito.remove', ['mueble' => 3]), [
            '_method' => 'DELETE',
            'sesionId' => $sesionId,
        ]);

        $response->assertRedirect(route('carrito.show', ['sesionId' => $sesionId]));
        $this->assertCount(0, Session::get($cartKey));
    }

    // =================================================================
    // Pruebas de Admin CRUD (Sesión)
    // =================================================================

    public function test_admin_puede_crear_un_mueble()
    {
        // Nota: Si usas mocks en Session::put, asegúrate de que Furniture::getMockData()
        // devuelva objetos que tu controlador entienda.

        [$sesionId, $user] = $this->loginAs('admin');

        // Inicializar sesión vacía o mockeada
        Session::put('muebles_crud_session', collect([]));

        $response = $this->post(route('admin.muebles.store'), [
            'sesionId' => $sesionId,
            'name' => 'Mueble de Prueba',
            'category_id' => 1,
            'description' => 'Desc',
            'price' => 99,
            'stock' => 10,
            'main_color' => 'Rojo',
        ]);

        $response->assertRedirect(route('admin.muebles.index', ['sesionId' => $sesionId]));

        // Verificamos que se haya añadido
        $this->assertCount(1, Session::get('muebles_crud_session'));
        $ultimoMueble = Session::get('muebles_crud_session')->last();

        // Ajusta esto dependiendo de si guardas Arrays u Objetos en la sesión
        // Si es array: $ultimoMueble['name']
        // Si es objeto: $ultimoMueble->name o $ultimoMueble->getName()
        // Asumo Objeto o Array, usaré data_get para seguridad
        $nombre = is_object($ultimoMueble) ? $ultimoMueble->name : $ultimoMueble['name'];
        $this->assertEquals('Mueble de Prueba', $nombre);
    }
}
