<?php

namespace App\Gateways;

use App\Core\Database;
use PDO;

class TransactionGateway
{
    private PDO $connection;

    public function __construct(Database $database)
    {
        $this->connection = $database->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->connection->query(
            "SELECT * FROM transactions ORDER BY created_at DESC"
        );

        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM transactions WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO transactions (type, coin, amount, price)
             VALUES (:type, :coin, :amount, :price)"
        );

        $stmt->execute([
            'type'   => $data['type'],
            'coin'   => $data['coin'],
            'amount' => $data['amount'],
            'price'  => $data['price'],
        ]);

        return (int)$this->connection->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare(
            "DELETE FROM transactions WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }
}
