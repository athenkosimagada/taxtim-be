<?php

class ErrorHandler
{
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        http_response_code(500);

        echo json_encode([
            "error" => true,
            "message" => $errstr,
            "file" => $errfile,
            "line" => $errline
        ]);

        return true;
    }

    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);

        echo json_encode([
            "error" => true,
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
    }
}
