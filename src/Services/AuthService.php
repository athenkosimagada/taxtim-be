<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Utils\JwtHelper;
use Exception;

class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function login(string $email, string $password): string
    {
        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid credentials');
        }

        return JwtHelper::generate([
            'userId' => $user['id'],
            'email'  => $user['email']
        ]);
    }

    public function register(string $email, string $password): void
    {
        if ($this->users->findByEmail($email)) {
            throw new Exception('Email already registered');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->users->create($email, $hash);
    }
}
