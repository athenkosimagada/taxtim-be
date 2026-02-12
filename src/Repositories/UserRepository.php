<?php

namespace App\Repositories;

use App\Database\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, email, password_hash
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function create(string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password_hash)
            VALUES (:email, :password_hash)
        ");

        $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash
        ]);

        return (int) $this->db->lastInsertId();
    }
}
