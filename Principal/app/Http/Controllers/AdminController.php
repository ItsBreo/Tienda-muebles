<?php

namespace App\Http\Controllers;

use App\Services\ApiFurnitureService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    protected ApiFurnitureService $apiFurniture;
    protected string $apiUsersUrl;

    public function __construct(ApiFurnitureService $apiFurniture)
    {
        $this->apiFurniture = $apiFurniture;
        $this->apiUsersUrl = env('API_USERS_URL', 'http://127.0.0.1:8001');
    }

    private function token(): ?string
    {
        return Session::get('token');
    }

    public function mueblesIndex(Request $request)
    {
        $search = $request->query('search');
        $filters = $search ? ['q' => $search] : [];
        $data = $this->apiFurniture->getFurnitureList($filters);
        $muebles = collect($data['data'] ?? [])->map(fn ($m) => (object) $m);

        return view('admin.muebles.index', [
            'muebles' => $muebles,
            'search' => $search,
        ]);
    }

    public function mueblesCreate(Request $request)
    {
        $categories = collect($this->apiFurniture->getCategories())->map(fn ($category) => (object) $category);

        return view('admin.muebles.create', [
            'categories' => $categories,
        ]);
    }

    public function mueblesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|integer',
            'main_color' => 'required|string|max:100',
        ]);

        $payload = $request->except(['_token', 'image']);

        if ($request->hasFile('image')) {
            $response = Http::withToken($this->token())
                ->attach('image', file_get_contents($request->file('image')->getRealPath()), $request->file('image')->getClientOriginalName())
                ->post($this->apiFurnitureUrl('/api/furniture'), $payload);
        } else {
            $response = Http::withToken($this->token())
                ->post($this->apiFurnitureUrl('/api/furniture'), $payload);
        }

        if ($response->successful()) {
            return redirect()->route('admin.muebles.index')
                ->with('success', 'Mueble creado correctamente.');
        }

        return back()->withErrors($response->json('errors', [
            'general' => $response->json('message', 'Error al crear el mueble.'),
        ]))->withInput();
    }

    public function mueblesShow($id, Request $request)
    {
        $mueble = $this->apiFurniture->getFurnitureDetail($id);
        if (!$mueble) {
            abort(404);
        }

        return view('admin.muebles.show', [
            'mueble' => $this->normalizarMueble($mueble),
        ]);
    }

    public function mueblesEdit($id, Request $request)
    {
        $mueble = $this->apiFurniture->getFurnitureDetail($id);
        $categories = collect($this->apiFurniture->getCategories())->map(fn ($category) => (object) $category);
        if (!$mueble) {
            abort(404);
        }

        return view('admin.muebles.edit', [
            'mueble' => $this->normalizarMueble($mueble),
            'categories' => $categories,
        ]);
    }

    public function mueblesUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|integer',
            'main_color' => 'required|string|max:100',
        ]);

        if ($request->hasFile('image')) {
            $response = Http::withToken($this->token())
                ->attach('image', file_get_contents($request->file('image')->getRealPath()), $request->file('image')->getClientOriginalName())
                ->put($this->apiFurnitureUrl("/api/furniture/{$id}"), $request->except(['_token', '_method', 'image']));
        } else {
            $response = Http::withToken($this->token())
                ->put($this->apiFurnitureUrl("/api/furniture/{$id}"), $request->except(['_token', '_method', 'image']));
        }

        if ($response->successful()) {
            return redirect()->route('admin.muebles.index')
                ->with('success', 'Mueble actualizado correctamente.');
        }

        return back()->withErrors($response->json('errors', [
            'general' => $response->json('message', 'Error al actualizar.'),
        ]))->withInput();
    }

    public function mueblesDestroy($id, Request $request)
    {
        Http::withToken($this->token())
            ->delete($this->apiFurnitureUrl("/api/furniture/{$id}"));

        return redirect()->route('admin.muebles.index')
            ->with('success', 'Mueble eliminado correctamente.');
    }

    public function categoriasIndex(Request $request)
    {
        $categorias = collect($this->apiFurniture->getCategories());

        return view('admin.categorias.index', [
            'categorias' => $categorias,
        ]);
    }

    public function categoriasCreate(Request $request)
    {
        return view('admin.categorias.create');
    }

    public function categoriasStore(Request $request)
    {
        $response = Http::withToken($this->token())
            ->post($this->apiFurnitureUrl('/api/categories'), $request->except(['_token']));

        if ($response->successful()) {
            return redirect()->route('admin.categorias.index')
                ->with('success', 'Categoria creada correctamente.');
        }

        return back()->withErrors([
            'general' => $response->json('message', 'Error al crear la categoria.'),
        ])->withInput();
    }

    public function categoriasShow($id, Request $request)
    {
        $response = Http::get($this->apiFurnitureUrl("/api/categories/{$id}"));
        $categoria = $response->successful() ? (object) $response->json('data') : null;
        if (!$categoria) {
            abort(404);
        }

        $categoria->created_at = !empty($categoria->created_at) ? Carbon::parse($categoria->created_at) : null;
        $categoria->updated_at = !empty($categoria->updated_at) ? Carbon::parse($categoria->updated_at) : null;
        $categoria->furniture = collect($categoria->furniture ?? [])->map(function ($mueble) {
            $mueble = (object) $mueble;
            $mueble->images = collect($mueble->images ?? [])->map(fn ($image) => (object) $image);
            return $mueble;
        });

        return view('admin.categorias.show', [
            'categoria' => $categoria,
        ]);
    }

    public function categoriasEdit($id, Request $request)
    {
        $response = Http::get($this->apiFurnitureUrl("/api/categories/{$id}"));
        $categoria = $response->successful() ? (object) $response->json('data') : null;
        if (!$categoria) {
            abort(404);
        }

        return view('admin.categorias.edit', [
            'categoria' => $categoria,
        ]);
    }

    public function categoriasUpdate(Request $request, $id)
    {
        $response = Http::withToken($this->token())
            ->put($this->apiFurnitureUrl("/api/categories/{$id}"), $request->except(['_token', '_method']));

        if ($response->successful()) {
            return redirect()->route('admin.categorias.index')
                ->with('success', 'Categoria actualizada correctamente.');
        }

        return back()->withErrors([
            'general' => $response->json('message', 'Error al actualizar.'),
        ])->withInput();
    }

    public function categoriasDestroy($id, Request $request)
    {
        Http::withToken($this->token())
            ->delete($this->apiFurnitureUrl("/api/categories/{$id}"));

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoria eliminada correctamente.');
    }

    public function usuariosIndex(Request $request)
    {
        $response = Http::withToken($this->token())
            ->get("{$this->apiUsersUrl}/api/users");

        $users = [];
        if ($response->successful()) {
            $users = collect($response->json('data') ?? [])->map(function ($u) {
                $user = (object) $u;
                $user->last_login_at = $user->last_login_at ? Carbon::parse($user->last_login_at) : null;
                $user->created_at = $user->created_at ? Carbon::parse($user->created_at) : null;
                if (isset($user->role)) {
                    $user->role = (object) $user->role;
                }
                return $user;
            });
        }

        return view('admin.usuarios.index', [
            'users' => $users,
        ]);
    }

    public function logs(Request $request)
    {
        $response = $this->apiFurniture->getActivityLogs();
        $rawLogs = $response['data']['data'] ?? [];
        $logs = collect($rawLogs)->map(fn ($log) => (object) $log);

        return view('admin.logs', [
            'logs' => $logs,
            'pagination' => $response['data'] ?? null,
        ]);
    }

    private function apiFurnitureUrl(string $path): string
    {
        return rtrim(env('API_FURNITURE_URL', 'http://127.0.0.1:8002'), '/') . $path;
    }

    private function normalizarMueble(array $mueble): object
    {
        $mueble = (object) $mueble;
        $mueble->images = collect($mueble->images ?? [])->map(fn ($image) => (object) $image);

        if (isset($mueble->category) && is_array($mueble->category)) {
            $mueble->category = (object) $mueble->category;
        }

        return $mueble;
    }
}
