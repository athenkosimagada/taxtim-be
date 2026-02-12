<?php

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $password_hash;

    public static function validate(array $data): bool
    { 
        $required = ['email', 'password'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new \InvalidArgumentException("Missing or empty field: $field");
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format");
        }

        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/'; 
        if (!preg_match($passwordRegex, $data['password'])) {
            throw new \InvalidArgumentException("Password must be at least 8 characters and include uppercase, lowercase, and a number");
        }
        return true;
    }
}