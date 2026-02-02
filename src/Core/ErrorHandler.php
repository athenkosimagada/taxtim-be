<?php

namespace App\Core;

use Throwable;

class ErrorHandler
{
    private static function isProduction(): bool
    {
        return getenv('APP_ENV') === 'production';
    }

    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
       Logger::error(
            "PHP Error [$errno]: {$errstr} in {$errfile}:{$errline}"
        );
        
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode([
            'error' => true,
            'message' => self::isProduction()
                ? 'An unexpected error occurred.'
                : $errstr,
        ]);

        return true;
    }

    public static function handleException(Throwable $exception): void
    {
         Logger::error(
            sprintf(
                'Uncaught %s: %s in %s:%d',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            )
        );

        http_response_code(500);

        echo json_encode([
            'error' => true,
            'message' => self::isProduction()
                ? 'Internal server error.'
                : $exception->getMessage(),
        ]);
    }
}
