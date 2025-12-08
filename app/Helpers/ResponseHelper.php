<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ResponseHelper
{
    /**
     * Generate standard success response structure.
     */
    public static function success(mixed $data = null): array
    {
        return [
            'success' => true,
            'error' => null,
            'data' => $data,
            'meta' => self::getMeta(),
        ];
    }

    /**
     * Generate standard error response structure.
     */
    public static function error(string $code, string $message, mixed $details = null): array
    {
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => self::getMeta(),
        ];
    }

    /**
     * Generate standard paginated response structure.
     */
    public static function paginated(mixed $data, array $pagination): array
    {
        return [
            'success' => true,
            'error' => null,
            'data' => $data,
            'pagination' => $pagination,
            'meta' => self::getMeta(),
        ];
    }

    /**
     * Generate standard metadata.
     */
    private static function getMeta(): array
    {
        return [
            'trace_id' => (string) Str::uuid(),
            'timestamp' => now()->timestamp,
        ];
    }
}
