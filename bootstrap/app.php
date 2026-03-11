<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Domain\Transaction\Exceptions\InsufficientFundsException;
use App\Domain\Transaction\Exceptions\ShopUserCannotTransferException;
use App\Domain\Transaction\Exceptions\UnauthorizedTransactionException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Always respond with JSON for API requests
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->wantsJson()
        );

        // 422 – business rule: insufficient funds
        $exceptions->render(function (InsufficientFundsException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
                'meta'    => ['timestamp' => now()->toISOString()],
            ], 422);
        });

        // 403 – shop users cannot initiate transfers
        $exceptions->render(function (ShopUserCannotTransferException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
                'meta'    => ['timestamp' => now()->toISOString()],
            ], 403);
        });

        // 403 – external authorizer denied the transaction
        $exceptions->render(function (UnauthorizedTransactionException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
                'meta'    => ['timestamp' => now()->toISOString()],
            ], 403);
        });

        // 422 – validation errors
        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
                'meta'    => ['timestamp' => now()->toISOString()],
            ], 422);
        });

        // 401 – unauthenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data'    => null,
                'meta'    => ['timestamp' => now()->toISOString()],
            ], 401);
        });

        // 404 – model not found
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            $model = class_basename($e->getModel());
            return response()->json([
                'success' => false,
                'message' => "{$model} not found.",
                'data'    => null,
                'meta'    => ['timestamp' => now()->toISOString()],
            ], 404);
        });

        // 500 – unhandled exception (never leaks stacktrace in production)
        $exceptions->render(function (\Throwable $e, $request) {
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred. Please try again later.',
                'data'    => config('app.debug') ? ['trace' => $e->getTraceAsString()] : null,
                'meta'    => ['timestamp' => now()->toISOString()],
            ], 500);
        });
    })
    ->create();
