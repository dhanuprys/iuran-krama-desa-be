<?php

namespace App\Helpers;

class ApiError
{
    /**
     * Get the error definition by key.
     *
     * @param string $key
     * @return array
     */
    public static function get(string $key): array
    {
        return config("api_errors.{$key}") ?? [
            'code' => 'ERR-UNKNOWN',
            'message' => 'Unknown error occurred',
            'http_code' => 500
        ];
    }

    /**
     * Get the error code by key.
     *
     * @param string $key
     * @return string
     */
    public static function code(string $key): string
    {
        return self::get($key)['code'];
    }

    /**
     * Get the error message by key.
     *
     * @param string $key
     * @return string
     */
    public static function message(string $key): string
    {
        return self::get($key)['message'];
    }
}
