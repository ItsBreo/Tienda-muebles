<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Services\ApiFurnitureService;

class AdminController extends Controller
{
    protected ApiFurnitureService $apiFurniture;
    protected string $apiUsersUrl;

    public function __construct(ApiFurnitureService $apiFurniture)
    {
        $this->apiFurniture = $apiFurniture;
        $this->apiUsersUrl  = env('API_USERS_URL', 'http://127.0.0.1:8001');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function token(): ?string
    {
        return Session::get('token');
    }

    private function sesionId(Request $request): ?string
    {
        return $request->query('sesionId', Session::get('session_id'));
    }

    // =========================================================================
    // MUEBLES
    // =========================================================================

    public function mueblesIndex(Request $request)
    {
        $search  = $request->query('search');
        $filters = $search ? ['q' => $search] : [];
        $data    = $this->apiFurniture->getFurnitureList($filters);
        $muebles = collect($data['data'] ?? [])->map(fn($m) => (object) $m);

        return view('admin.muebles.index', [
            'muebles'  => $muebles,
            'search'   => $search,
            'sesionId' => $this->sesionId($request),
        ]);
    }

    public function mueblesCreate(Request $request)
    {
        $categories = collect($this->apiFurniture->getCategories());

        return view('admin.muebles.create', [
            'categories' => $categories,
            'sesionId'   => $this->sesionId($request),
        ]);
    }

    public function mueblesStore(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|integer',
            'main_color'  => 'required|string|max:100',
        ]);

        $response = Http::withToken($this->token())
            ->attach('image', $request->hasFile('image') ? file_get_contents($request->file('image')->getRealPath()) : null, $request->hasFile('image') ? $request->file('image')->getClientOriginalName() : null)
            ->post("{$this->apiUsersUrl}/api/furniture", $request->except(['_token', 'image']));

        // Si no hay imagen, usar POST normal
        if ($request->hasFile('image')) {
            $response = Http::withToken($this->token())
                ->attach('image', file_get_contents($request->file('image')->getRealPath()), $request->file('image')->getClientOriginalName())
                ->post("http://127.0.0.1:8000/api/furniture", $request->except(['_token', 'image']));
        } else {
            $response = Http::withToken($this->token())
                ->post("http://127.0.0.1:8000/api/furniture", $request->except(['_token', 'image']));
        }

        if ($response->successful()) {
            return redirect()->route('admin.muebles.index', ['sesionId' => $this->sesionId($request)])
                ->with('success', 'Mueble creado correctamente.');
        }

        return back()->withErrors($response->json('errors', ['general' => $response->json('message', 'Error al crear el mueble.')]))->withInput();
    }

    public function mueblesShow($id, Request $request)
    {
        $mueble = $this->apiFurniture->getFurnitureDetail($id);
        if (!$mueble) abort(404);

        return view('admin.muebles.show', [
            'mueble'   => (object) $mueble,
            'sesionId' => $this->sesionId($request),
        ]);
    }

    public function mueblesEdit($id, Request $request)
    {
        $mueble     = $this->apiFurniture->getFurnitureDetail($id);
        $categories = collect($this->apiFurniture->getCategories());
        if (!$mueble) abort(404);

        return view('admin.muebles.edit', [
            'mueble'     => (object) $mueble,
            'categories' => $categories,
            'sesionId'   => $this->sesionId($request),
        ]);
    }

    public function mueblesUpdate(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|integer',
            'main_color'  => 'required|string|max:100',
        ]);

        if ($request->hasFile('image')) {
            $response = Http::withToken($this->token())
                ->attach('image', file_get_contents($request->file('image')->getRealPath()), $request->file('image')->getClientOriginalName())
                ->put("http://127.0.0.1:8000/api/furniture/{$id}", $request->except(['_token', '_method', 'image']));
        } else {
            $response = Http::withToken($this->token())
                ->put("http://127.0.0.1:8000/api/furniture/{$id}", $request->except(['_token', '_method', 'image']));
        }

        if ($response->successful()) {
            return redirect()->route('admin.muebles.index', ['sesionId' => $this->sesionId($request)])
                ->with('success', 'Mueble actualizado correctamente.');
        }

        return back()->withErrors($response->json('errors', ['general' => $response->json('message', 'Error al actualizar.')]))->withInput();
    }

    public function mueblesDestroy($id, Request $request)
    {
        Http::withToken($this->token())
            ->delete("http://127.0.0.1:8000/api/furniture/{$id}");

        return redirect()->route('admin.muebles.index', ['sesionId' => $this->sesionId($request)])
            ->with('success', 'Mueble eliminado correctamente.');
    }

    // =========================================================================
    // CATEGORÍAS
    // =========================================================================

    public function categoriasIndex(Request $request)
    {
        $categorias = collect($this->apiFurniture->getCategories());

        return view('admin.categorias.index', [
            'categorias' => $categorias,
            'sesionId'   => $this->sesionId($request),
        ]);
    }

    public function categoriasCreate(Request $request)
    {
        return view('admin.categorias.create', [
            'sesionId' => $this->sesionId($request),
        ]);
    }

    public function categoriasStore(Request $request)
    {
        $response = Http::withToken($this->token())
            ->post("http://127.0.0.1:8000/api/categories", $request->except('_token'));

        if ($response->successful()) {
            return redirect()->route('admin.categorias.index', ['sesionId' => $this->sesionId($request)])
                ->with('success', 'Categoría creada correctamente.');
        }

        return back()->withErrors(['general' => $response->json('message', 'Error al crear la categoría.')])->withInput();
    }

    public function categoriasShow($id, Request $request)
    {
        $response = Http::get("http://127.0.0.1:8000/api/categories/{$id}");
        $categoria = $response->successful() ? (object) $response->json('data') : null;
        if (!$categoria) abort(404);

        return view('admin.categorias.show', [
            'categoria' => $categoria,
            'sesionId'  => $this->sesionId($request),
        ]);
    }

    public function categoriasEdit($id, Request $request)
    {
        $response = Http::get("http://127.0.0.1:8000/api/categories/{$id}");
        $categoria = $response->successful() ? (object) $response->json('data') : null;
        if (!$categoria) abort(404);

        return view('admin.categorias.edit', [
            'categoria' => $categoria,
            'sesionId'  => $this->sesionId($request),
        ]);
    }

    public function categoriasUpdate(Request $request, $id)
    {
        $response = Http::withToken($this->token())
            ->put("http://127.0.0.1:8000/api/categories/{$id}", $request->except(['_token', '_method']));

        if ($response->successful()) {
            return redirect()->route('admin.categorias.index', ['sesionId' => $this->sesionId($request)])
                ->with('success', 'Categoría actualizada correctamente.');
        }

        return back()->withErrors(['general' => $response->json('message', 'Error al actualizar.')])->withInput();
    }

    public function categoriasDestroy($id, Request $request)
    {
        Http::withToken($this->token())
            ->delete("http://127.0.0.1:8000/api/categories/{$id}");

        return redirect()->route('admin.categorias.index', ['sesionId' => $this->sesionId($request)])
            ->with('success', 'Categoría eliminada correctamente.');
    }

    // =========================================================================
    // USUARIOS
    // =========================================================================

    public function usuariosIndex(Request $request)
    {
        $response = Http::withToken($this->token())
            ->get("{$this->apiUsersUrl}/api/users");

        $users = [];
        if ($response->successful()) {
            $users = collect($response->json('data') ?? [])->map(fn($u) => (object) $u);
        }

        return view('admin.usuarios.index', [
            'users'    => $users,
            'sesionId' => $this->sesionId($request),
        ]);
    }
}
