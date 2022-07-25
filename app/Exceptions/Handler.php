<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e ): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Sorry this data do not exist',
                'success' => false
            ], 404);
        }
        if ($e instanceof QueryException) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' =>  false
            ], 401);
        }
        if ($e instanceof BindingResolutionException) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' =>  false
            ], 401);
        }
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' =>  false
            ], 401);
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' =>  false
            ], 401);
        }
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Route not found',
                'success' =>  false
            ], 401);
        }
        if ($e instanceof RouteNotFoundException) {
            return response([
                'message' => $e->getMessage(),
                'success' =>  false
            ], 401);
        }
        if ($e instanceof AuthenticationException) {
            return  response()->json([
                'message' =>  $e->getMessage(),
                'success' => false
            ], 401);
        }
        return  response()->json([
            'message' => 'Sorry there was an  error contact the backend team',
            'success' => false
        ], 401);
    }
}
