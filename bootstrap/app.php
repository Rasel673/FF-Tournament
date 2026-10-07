<?php

use App\Http\Resources\ErrorResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
         $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ErrorResource::send($e->validator->errors()->first(), 422, $e->errors());
            }

            if ($e instanceof AuthenticationException) {
                return ErrorResource::send('Unauthenticated. Please login again.', 401);
            }

            if ($e instanceof HttpExceptionInterface) {
                $code = $e->getStatusCode();
                $message = $code === 404
                    ? 'Resource not found.'
                    : ($e->getMessage() ?: (Response::$statusTexts[$code] ?? 'Error'));

                return ErrorResource::send($message, $code);
            }

            report($e);

            return ErrorResource::send(
                config('app.debug') ? $e->getMessage() : 'Server error. Please try again later.',
                500
            );
        });
    })->create();
