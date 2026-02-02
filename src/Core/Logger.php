<?php

namespace App\Core;

class Logger
{
    private static function timestamp(): string
    {
        return date('D M j H:i:s Y');
    }

     private static function client(): string
    {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $port = $_SERVER['REMOTE_PORT'] ?? '0';

        return "{$ip}:{$port}";
    }

    private static function write(
        string $level,
        string $message
    ): void {
        $line = sprintf(
            '[%s] [%s] %s %s',
            self::timestamp(),
            $level,
            self::client(),
            $message
        );

        error_log($line);
    }

     public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    public static function debug(string $message): void
    {
        if (getenv('APP_ENV') !== 'production') {
            self::write('DEBUG', $message);
        }
    }
}