<?php

class TransactionGateway
{
    private PDO $connection;

    public function __construct(Database $database)
    {
        $this->connection = $database->getConnection();
    }
    public function getAll(): array
    {
        // Implementation to retrieve all transactions from the data source
        return [
            [
                "id" => 1,
                "type" => "buy",
                "coin" => "BTC",
                "amount" => 1.0,
                "price" => 10000.00,
                "created_at" => "2023-01-01 00:00:00"
            ],
            [
                "id" => 2,
                "type" => "sell",
                "coin" => "ETH",
                "amount" => 2.5,
                "price" => 1800.00,
                "created_at" => "2023-01-02 12:30:00"
            ]
        ];
    }

    public function create(array $data): int
    {
        return rand(1, 1000);
    }
}