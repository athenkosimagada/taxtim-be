<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private ?PDO $connection = null;
     public function __construct(
        private string $host,
        private string $dbName,
        private string $user,
        private string $password
    ) {}
    
    public function getConnection(): PDO
    {
       if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::ATTR_PERSISTENT => true,
                ];
            return new PDO($dsn, $this->user, $this->password, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
       }

       return $this->connection;
    }
}