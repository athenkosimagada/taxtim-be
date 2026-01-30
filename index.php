<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

header("Content-type: application/json; charset=UTF-8");

$parts = explode("/", $_SERVER["REQUEST_URI"]);

if ($parts[1] != "transactions") {
    http_response_code(404);
    exit;
}

$id = $parts[2] ?? null;

$database = new Database(
    "mysql",              // service name
    "crypto_tax",         // DB name from docker-compose
    "crypto_user",        // DB user
    "crypto_password"     // DB password
);

$gateway = new TransactionGateway($database);

$controller = new TransactionController($gateway);

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);