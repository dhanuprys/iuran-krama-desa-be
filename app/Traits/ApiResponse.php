<?php

namespace App\Traits;

use App\Helpers\ApiError;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function success(mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(ResponseHelper::success($data), $code);
    }

    /**
     * Send an error response.
     *
     * @param string $key Error key from config/api_errors.php
     * @param mixed $details Additional error details (e.g. validation errors)
     * @param string|null $messageOverride Override default message
     * @param int|null $codeOverride Override default HTTP code
     * @return JsonResponse
     */
    protected function error(string $key, mixed $details = null, ?string $messageOverride = null, ?int $codeOverride = null): JsonResponse
    {
        $errorDef = ApiError::get($key);

        $response = ResponseHelper::error(
            $errorDef['code'],
            $messageOverride ?? $errorDef['message'],
            $details
        );

        return response()->json($response, $codeOverride ?? $errorDef['http_code']);
    }

    /**
     * Send a paginated response.
     *
     * @param ResourceCollection|LengthAwarePaginator $resource
     * @param int $code
     * @return JsonResponse
     */
    protected function paginated(mixed $resource, int $code = 200): JsonResponse
    {
        $pagination = [];
        $data = $resource;

        $paginator = null;

        if ($resource instanceof ResourceCollection) {
            $paginator = $resource->resource;
            // Resolve the resource to get the transformed data array
            $data = $resource->resolve();
        } elseif ($resource instanceof LengthAwarePaginator) {
            $paginator = $resource;
            $data = $resource->items();
        }

        if ($paginator instanceof LengthAwarePaginator) {
            $pagination = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];
        }

        return response()->json(ResponseHelper::paginated($data, $pagination), $code);
    }
}
