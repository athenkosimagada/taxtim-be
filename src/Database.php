<?php

class Database
{
    public function __construct(private string $host, 
                                private string $dbName, 
                                private string $user, 
                                private string $password)
    {}

    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];
        return new PDO($dsn, $this->user, $this->password, $options);
    }
}