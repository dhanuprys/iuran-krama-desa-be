<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Error Codes
    |--------------------------------------------------------------------------
    |
    | This file contains the dictionary of all internal error codes used by the API.
    | Each error has a unique code, a description, and a default HTTP status code.
    |
    */

    // General Errors
    'VALIDATION_ERROR' => [
        'code' => 'ERR-VAL-001',
        'message' => 'Validation failed',
        'http_code' => 422
    ],
    'RESOURCE_NOT_FOUND' => [
        'code' => 'ERR-RES-001',
        'message' => 'Resource not found',
        'http_code' => 404
    ],
    'SERVER_ERROR' => [
        'code' => 'ERR-SYS-001',
        'message' => 'Internal server error',
        'http_code' => 500
    ],

    // Authentication & Authorization
    'UNAUTHORIZED' => [
        'code' => 'ERR-AUTH-001',
        'message' => 'User not authenticated',
        'http_code' => 401
    ],
    'FORBIDDEN' => [
        'code' => 'ERR-AUTH-002',
        'message' => 'Access denied',
        'http_code' => 403
    ],
    'FORBIDDEN_ACCESS' => [
        'code' => 'ERR-AUTH-003',
        'message' => 'Access denied to this resource',
        'http_code' => 403
    ],

    // Business Logic Errors
    'RESIDENT_DUPLICATE' => [
        'code' => 'ERR-BIZ-001',
        'message' => 'Resident already exists',
        'http_code' => 409
    ],
    'INVOICE_DUPLICATE' => [
        'code' => 'ERR-BIZ-002',
        'message' => 'Invoice already exists for this period',
        'http_code' => 409
    ],
    'RESIDENT_HAS_INVOICES' => [
        'code' => 'ERR-BIZ-003',
        'message' => 'Cannot delete resident with existing invoices',
        'http_code' => 422
    ],
    'BANJAR_HAS_RESIDENTS' => [
        'code' => 'ERR-BIZ-004',
        'message' => 'Cannot delete banjar with associated residents',
        'http_code' => 422
    ],
    'INVALID_STATUS' => [
        'code' => 'ERR-BIZ-005',
        'message' => 'Invalid status transition',
        'http_code' => 422
    ],
];
