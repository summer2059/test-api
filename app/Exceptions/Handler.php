<?php

namespace App\Exceptions;

use App\Traits\ApiResponses;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponses;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Map of exception types to handler methods.
     *
     * @var array<class-string, string>
     */
    protected $handlers = [
        ValidationException::class => 'handleValidation',
        ModelNotFoundException::class => 'handleModelNotFound',
        AuthorizationException::class => 'notAuthorized',
    ];

    /**
     * Handle validation exceptions.
     */
    private function handleValidation(ValidationException $e)
    {
        $errors = [];

        foreach ($e->errors() as $key => $messages) {
            foreach ($messages as $message) {
                $errors[] = [
                    'status' => 422,
                    'message' => $message,
                    'source' => $key,
                ];
            }
        }

        return $errors;
    }

    /**
     * Handle model not found exceptions.
     */
    private function handleModelNotFound(ModelNotFoundException $e)
    {
        return [
            [
                'status' => 404,
                'message' => 'The requested resource was not found.',
                'source' => $e->getModel(),
            ]
        ];
    }

    /**
     * Handle authorization exceptions.
     */
    private function notAuthorized(AuthorizationException $e)
    {
        return [
            [
                'status' => 403,
                'message' => 'You are not authorized to perform this action.',
                'source' => 'Authorization',
            ]
        ];
    }

    /**
     * Handle unauthenticated exceptions.
     * Must be protected (not private).
     */
    protected function unauthenticated($request, AuthenticationException $e)
    {
        return $this->error([
            [
                'status' => 401,
                'message' => 'Unauthenticated.',
                'source' => 'Authentication',
            ]
        ]);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        $className = get_class($e);

        if (array_key_exists($className, $this->handlers)) {
            $method = $this->handlers[$className];
            return $this->error($this->$method($e));
        }

        // Default fallback for any unhandled exception
        return $this->error([
            'type' => class_basename($e),
            'status' => 0,
            'message' => $e->getMessage(),
        ]);
    }
}
