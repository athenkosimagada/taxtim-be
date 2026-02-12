<?php

namespace App\Middleware;

use App\Utils\JwtHelper;

class AuthMiddleware
{
    public static function handle(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing token']);
            exit;
        }

        try {
            JwtHelper::validate($matches[1]);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}
