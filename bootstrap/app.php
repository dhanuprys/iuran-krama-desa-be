<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                // SQL State 23000 is for integrity constraint violation
                if ($e->getCode() === '23000') {
                    // Check for foreign key constraint violation
                    if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                        // Use the ApiError helper if available, or fallback to manual response
                        // We can't easily use the Trait here since we are in a closure, so we construct the response manually
                        // matching the ApiResponse trait structure.
    
                        return response()->json(\App\Helpers\ResponseHelper::error(
                            'ERR-DB-001',
                            'Data cannot be deleted or updated because it is referenced by other records.'
                        ), 409); // 409 Conflict
                    }
                }
            }
        });
    })->create();
