<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Furniture;
use App\Models\Category;

class Test extends TestCase
{
    /**
     * ¡Función Helper Clave!
     * Simula nuestro flujo de login manual y devuelve el sesionId.
     *
     * @param string $role 'user' o 'admin'
     * @return array [string $sesionId, User $user, string $cookieName]
     */
    private function loginAs($role = 'user')
    {
        // 1. Obtenemos los datos del mock de usuario
        $email = ($role === 'admin') ? 'admin@correo.com' : 'jose@correo.com';
        $password = '1234';
        $user = User::verifyUser($email, $password); // Obtenemos la instancia del User

        // 2. Simulamos el post de login
        $response = $this->post(route('login.store'), [
            'email' => $email,
            'password' => $password,
        ]);

        // 3. Capturamos el sesionId de la URL de redirección
        // (Ej: http://localhost/principal?sesionId=... )
        $location = $response->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $sesionId = $query['sesionId'];
        $cookieName = 'preferencias_' . $user->getId();

        // 4. Devolvemos el sesionId y el usuario para usarlo en los tests
        return [$sesionId, $user, $cookieName];
    }

    // =================================================================
    // Pruebas de Login, Logout y Seguridad (Requisito 2 y 5)
    // =================================================================

    public function test_login_fallido_credenciales_invalidas()
    {
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
        $response = $this->post(route('login.store'), [
            'email' => 'jose@correo.com',
            'password' => '1234',
        ]);

        // 1. Debe redirigir (a principal o preferencias)
        $response->assertStatus(302);

        // 2. Comprobamos que el array 'usuarios' en la sesión ahora tiene un miembro
        $this->assertCount(1, Session::get('usuarios'));
    }

    public function test_logout_limpia_sesion_de_usuario_especifico()
    {
        // 1. Iniciamos sesión como 'user'
        [$sesionId, $user] = $this->loginAs('user');
        $this->assertNotNull(Session::get('usuarios')[$sesionId]);

        // 2. Hacemos logout con ese sesionId
        $response = $this->post(route('login.logout'), [
            'sesionId' => $sesionId,
        ]);

        // 3. Comprobamos que ESE usuario ya no está en la sesión
        $response->assertRedirect(route('principal'));
        $this->assertFalse(isset(Session::get('usuarios')[$sesionId]));
    }

    public function test_admin_bloqueado_sin_sesion()
    {
        // 1. Intentamos acceder al index de admin sin sesionId
        $response = $this->get(route('admin.muebles.index'));

        // 2. Debe redirigir al login con un error
        $response->assertRedirect(route('login.show'));
        $response->assertSessionHas('error');
    }

    public function test_admin_bloqueado_con_rol_usuario()
    {
        // 1. Iniciamos sesión como usuario NORMAL
        [$sesionId, $user] = $this->loginAs('user');

        // 2. Intentamos acceder al admin con el sesionId del usuario
        $response = $this->get(route('admin.muebles.index', ['sesionId' => $sesionId]));

        // 3. Debe redirigir a 'principal' con un error de admin
        $response->assertRedirect(route('principal', ['sesionId' => $sesionId]));
        $response->assertSessionHas('error-admin');
    }

    public function test_admin_acceso_exitoso_con_rol_admin()
    {
        // 1. Iniciamos sesión como ADMIN
        [$sesionId, $user] = $this->loginAs('admin');

        // 2. Intentamos acceder al admin con el sesionId del admin
        $response = $this->get(route('admin.muebles.index', ['sesionId' => $sesionId]));

        // 3. Debe cargar la vista correctamente (OK 200)
        $response->assertStatus(200);
        $response->assertViewIs('admin.muebles.index');
    }

    // =================================================================
    // Pruebas de Cookies y Preferencias (Requisito 1)
    // =================================================================

    public function test_preferencias_cambia_tema_y_moneda()
    {
        // 1. Iniciamos sesión
        [$sesionId, $user, $cookieName] = $this->loginAs('user');

        // 2. Enviamos el formulario de preferencias
        // !! CORRECCIÓN: La ruta se llama 'preferencias.update', no 'preferencias.store' !!
        $response = $this->post(route('preferencias.update'), [
            'sesionId' => $sesionId,
            'tema' => 'oscuro',
            'moneda' => 'USD',
            'tamaño' => 12,
        ]);

        // 3. Comprobamos la redirección
        $response->assertRedirect(route('principal', ['sesionId' => $sesionId]));

        // 4. Comprobamos que la respuesta LLEVA la cookie actualizada
        $response->assertCookie($cookieName);
        $cookie = $response->getCookie($cookieName);
        $this->assertStringContainsString('"tema":"oscuro"', $cookie->getValue());
        $this->assertStringContainsString('"moneda":"USD"', $cookie->getValue());
    }

    public function test_preferencias_paginacion_se_aplica_al_catalogo()
    {
        // 1. Iniciamos sesión
        [$sesionId, $user, $cookieName] = $this->loginAs('user');

        // 2. Creamos una cookie de preferencia con 12 por página
        $preferencias = json_encode(['tamaño' => 12]);

        // 3. Hacemos la petición al catálogo, pasando el sesionId y la cookie
        $response = $this->withCookie($cookieName, $preferencias)
                         ->get(route('muebles.index', ['sesionId' => $sesionId]));

        // 4. Comprobamos que el paginador se configuró a 12
        $response->assertStatus(200);
        $paginator = $response->viewData('muebles'); // Obtenemos la variable $muebles
        $this->assertEquals(12, $paginator->perPage());
    }

    // =================================================================
    // Pruebas de Catálogo (Requisito 3)
    // =================================================================

    public function test_catalogo_filtra_por_categoria()
    {
        [$sesionId, $user] = $this->loginAs('user');

        // 1. Pedimos la categoría 1
        $response = $this->get(route('muebles.index', [
            'sesionId' => $sesionId,
            'category' => 1
        ]));

        $response->assertStatus(200);

        // 2. Comprobamos que todos los muebles en la vista son de esa categoría
        $muebles = $response->viewData('muebles');
        foreach ($muebles as $mueble) {
            $this->assertEquals(1, $mueble->getCategoryId());
        }
    }

    public function test_catalogo_ordena_por_precio_asc()
    {
        [$sesionId, $user] = $this->loginAs('user');

        // 1. Pedimos ordenar por precio ascendente
        $response = $this->get(route('muebles.index', [
            'sesionId' => $sesionId,
            'sort' => 'price_asc'
        ]));

        $response->assertStatus(200);

        // 2. Comprobamos que los precios están en orden
        $muebles = $response->viewData('muebles')->items();
        $this->assertGreaterThanOrEqual($muebles[0]->getPrice(), $muebles[1]->getPrice());
        $this->assertGreaterThanOrEqual($muebles[1]->getPrice(), $muebles[2]->getPrice());
    }

    // =================================================================
    // Pruebas de Carrito (Requisito 4)
    // =================================================================

    public function test_carrito_anadir_producto()
    {
        [$sesionId, $user] = $this->loginAs('user');
        $cartKey = 'carrito_' . $user->getId();

        // 1. Hacemos POST para añadir el mueble ID 3
        $response = $this->post(route('carrito.add', ['mueble' => 3]), [
            'sesionId' => $sesionId,
            'quantity' => 1,
        ]);

        // 2. Comprobamos la redirección y que la sesión del carrito tiene el item
        $response->assertRedirect(route('carrito.show', ['sesionId' => $sesionId]));
        $this->assertNotNull(Session::get($cartKey)[3]);
        $this->assertEquals(1, Session::get($cartKey)[3]['cantidad']);
    }

    public function test_carrito_eliminar_item()
    {
        [$sesionId, $user] = $this->loginAs('user');
        $cartKey = 'carrito_' . $user->getId();

        // 1. "Sembramos" el carrito con un item
        Session::put($cartKey, [
            3 => ['id' => 3, 'nombre' => 'Test', 'precio' => 100, 'cantidad' => 1]
        ]);
        $this->assertCount(1, Session::get($cartKey));

        // 2. Hacemos DELETE (vía POST con _method) para eliminar el mueble 3
        // !! CORRECCIÓN: La ruta es POST (con _method: DELETE), no un DELETE nativo !!
        $response = $this->post(route('carrito.remove', ['mueble' => 3]), [
            '_method' => 'DELETE',
            'sesionId' => $sesionId,
        ]);

        // 3. Comprobamos que el carrito está vacío
        $response->assertRedirect(route('carrito.show', ['sesionId' => $sesionId]));
        $this->assertCount(0, Session::get($cartKey));
    }

    // =================================================================
    // Pruebas de Admin CRUD (Requisito 5)
    // (Estas usan la "BD de sesión" muebles_crud_session)
    // =================================================================

    public function test_admin_puede_crear_un_mueble()
    {
        [$sesionId, $user] = $this->loginAs('admin');

        // 1. Sembramos la BD de sesión con los mocks (12 muebles)
        $mueblesMock = collect(Furniture::getMockData());
        Session::put('muebles_crud_session', $mueblesMock);
        $this->assertCount(12, Session::get('muebles_crud_session'));

        // 2. Hacemos POST para crear un nuevo mueble
        $response = $this->post(route('admin.muebles.store'), [
            'sesionId' => $sesionId,
            'name' => 'Mueble de Prueba',
            'category_id' => 1,
            'description' => 'Desc',
            'price' => 99,
            'stock' => 10,
            'main_color' => 'Rojo',
        ]);

        // 3. Comprobamos que la BD de sesión ahora tiene 13 muebles
        $response->assertRedirect(route('admin.muebles.index', ['sesionId' => $sesionId]));
        $this->assertCount(13, Session::get('muebles_crud_session'));
        // 4. Comprobamos que el último mueble es el que creamos
        $this->assertEquals('Mueble de Prueba', Session::get('muebles_crud_session')->last()->getName());
    }

    public function test_admin_puede_eliminar_un_mueble()
    {
        [$sesionId, $user] = $this->loginAs('admin');

        // 1. Sembramos la BD de sesión con los mocks (12 muebles)
        $mueblesMock = collect(Furniture::getMockData());
        Session::put('muebles_crud_session', $mueblesMock);
        $this->assertCount(12, Session::get('muebles_crud_session'));

        // 2. Hacemos DELETE para eliminar el mueble ID 5
        $response = $this->delete(route('admin.muebles.destroy', ['id' => 5]), [
            'sesionId' => $sesionId,
        ]);

        // 3. Comprobamos que la BD de sesión ahora tiene 11 muebles
        $response->assertRedirect(route('admin.muebles.index', ['sesionId' => $sesionId]));
        $this->assertCount(11, Session::get('muebles_crud_session'));
        // 4. Comprobamos que el mueble 5 ya no existe
        $this->assertNull(Session::get('muebles_crud_session')->firstWhere('id', 5));
    }
}
