<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    // ──────────────────────────────────────────────────────────────────────────
    // Respuestas de éxito
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Respuesta estándar de éxito con datos.
     */
    protected function okResponse(mixed $data, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Respuesta 201 Created.
     */
    protected function createdResponse(mixed $data, string $message = 'Recurso creado correctamente.'): JsonResponse
    {
        return $this->okResponse($data, $message, 201);
    }

    /**
     * Respuesta de solo mensaje (sin datos), típico para DELETE.
     */
    protected function messageResponse(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $status);
    }

    /**
     * Respuesta paginada con metadatos de filtros y paginación.
     * Acepta un paginator de Eloquent y la clase Resource a aplicar.
     *
     * @param  class-string  $resourceClass
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $resourceClass,
        array $filters = []
    ): JsonResponse {
        // Resolvemos el resource sobre el paginator
        $items = $resourceClass::collection($paginator)->resolve(request());

        // Limpiamos los filtros nulos para no saturar la respuesta
        $activeFilters = array_filter(
            $filters,
            fn($v) => $v !== null && $v !== ''
        );

        $payload = [
            'success' => true,
            'data'    => $items,
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ];

        if (!empty($activeFilters)) {
            $payload['filters'] = $activeFilters;
        }

        return response()->json($payload, 200);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Respuestas de error
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Error genérico.
     */
    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * 404 Not Found.
     */
    protected function notFoundResponse(string $entity = 'Recurso'): JsonResponse
    {
        return $this->errorResponse("{$entity} no encontrado.", 404);
    }

    /**
     * 422 Unprocessable Entity — errores de validación.
     */
    protected function validationErrorResponse(array $errors, string $message = 'Los datos proporcionados no son válidos.'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * 401 Unauthorized.
     */
    protected function unauthorizedResponse(string $message = 'No autenticado.'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * 403 Forbidden.
     */
    protected function forbiddenResponse(string $message = 'No tienes permisos para esta acción.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * 500 Server Error.
     */
    protected function serverErrorResponse(string $message = 'Error interno del servidor.'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }
}
