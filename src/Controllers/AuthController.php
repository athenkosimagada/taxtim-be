<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }

        try {
            $token = $this->auth->login($input['email'], $input['password']);

            echo json_encode([
                'success' => true,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function register(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            return;
        }

        try {
            $this->auth->register($input['email'], $input['password']);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
