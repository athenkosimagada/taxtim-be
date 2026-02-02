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

        $message = self::isProduction()
            ? 'An unexpected error occurred.'
            : self::sanitizeMessage($errstr);

        echo json_encode([
            'error' => true,
            'message' => $message,
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

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
        }

        $message = self::isProduction()
            ? 'Internal server error.'
            : self::sanitizeMessage($exception->getMessage());

        echo json_encode([
            'error' => true,
            'message' => $message,
        ]);
    }

    private static function sanitizeMessage(string $message): string
    {
        $pattern = '#[a-zA-Z]:\\\\(?:[^\\\\/:*?"<>|\r\n]+\\\\)*[^\\\\/:*?"<>|\r\n]+#'; // Windows paths
        $message = preg_replace($pattern, '[path]', $message);

         $patternUnix = '#\/(?:[\w.-]+\/)*[\w.-]+#';
        $message = preg_replace($patternUnix, '[path]', $message);

        return $message;
    }
}
