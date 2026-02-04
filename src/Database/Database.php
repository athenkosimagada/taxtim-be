<?php

namespace App\Database;

use App\Config\Env;
use PDO;

class Database
{
    private static ?PDO $conn = null;
    
    public static function connection(): PDO
    {
        if (!self::$conn) {
            $host = Env::get('MYSQL_HOST', 'mysql');
            $port = Env::get('MYSQL_PORT', '3306');
            $dbname = Env::get('MYSQL_DATABASE', 'crypto_tax');
            $user = Env::get('MYSQL_USER', 'crypto_user');
            $password = Env::get('MYSQL_PASSWORD', '');

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

            $maxAttempts = 5;
            $attempt = 0;
            $connected = false;
            while (!$connected && $attempt < $maxAttempts) {
                try {
                    self::$conn = new PDO($dsn, $user, $password);
                    $connected = true;
                } catch (\PDOException $e) {
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        throw $e;
                    }
                    sleep(3);
                }
            }
        }
        return self::$conn;
    }
}