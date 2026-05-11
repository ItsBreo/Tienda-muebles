<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiFurnitureService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('API_FURNITURE_URL', 'http://api_furniture'), '/');
    }

    /**
     * Obtiene la lista de categorías.
     */
    public function getCategories()
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/api/categories');
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching categories from api_furniture: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Obtiene los muebles destacados.
     */
    public function getFeatured()
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/api/furniture/featured');
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching featured furniture from api_furniture: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Obtiene el catálogo de muebles con filtros y paginación.
     */
    public function getFurnitureList(array $filters = [])
    {
        try {
            $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
            $response = Http::timeout(5)->get($this->baseUrl . '/api/furniture', $filters);
            if ($response->successful()) {
                // Devuelve el array completo porque incluye metadata de paginación
                return $response->json() ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching furniture list from api_furniture: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Obtiene los colores disponibles.
     */
    public function getColors()
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/api/furniture/colors');
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching colors from api_furniture: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Obtiene el detalle de un mueble específico.
     */
    public function getFurnitureDetail($id)
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/api/furniture/' . $id);
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching furniture detail from api_furniture: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Obtiene los logs de actividad del sistema.
     */
    public function getActivityLogs()
    {
        try {
            $token = session('token');
            if (!$token) {
                return [];
            }

            $response = Http::timeout(5)
                ->withToken($token)
                ->get($this->baseUrl . '/api/activity-logs');

            if ($response->successful()) {
                return $response->json() ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching activity logs from api_furniture: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Obtiene los detalles de varios muebles por sus IDs.
     */
    public function getMultipleFurniture(array $ids)
    {
        if (empty($ids)) return collect([]);

        $results = [];
        foreach ($ids as $id) {
            $mueble = $this->getFurnitureDetail($id);
            if ($mueble) {
                $results[$id] = $mueble;
            }
        }

        return collect($results);
    }

    /**
     * Llama a la API para descontar el stock tras una compra.
     */
    public function decrementStock(array $items)
    {
        try {
            $response = Http::timeout(5)->post($this->baseUrl . '/api/furniture/decrement-stock', [
                'items' => $items
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Error decrementing stock in api_furniture: ' . $e->getMessage());
            return false;
        }
    }
}
