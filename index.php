<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Autoload
|--------------------------------------------------------------------------
*/
spl_autoload_register(function (string $class): void {
    $base = __DIR__ . '/src/';

    $paths = [
        $base,
        $base . 'Controllers/',
        $base . 'Gateways/',
        $base . 'Core/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

/*
|--------------------------------------------------------------------------
| Error Handling
|--------------------------------------------------------------------------
*/
set_error_handler(['ErrorHandler', 'handleError']);
set_exception_handler(['ErrorHandler', 'handleException']);

header('Content-Type: application/json; charset=UTF-8');

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/
$database = new Database(
    getenv('MYSQL_HOST') ?: 'mysql',
    getenv('MYSQL_DATABASE') ?: 'crypto_tax',
    getenv('MYSQL_USER') ?: 'crypto_user',
    getenv('MYSQL_PASSWORD') ?: 'crypto_password'
);

$transactionGateway    = new TransactionGateway($database);
$transactionController = new TransactionController($transactionGateway);

/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/
$router = new Router();

$router->add('GET',  '/transactions',        [$transactionController, 'index']);
$router->add('POST', '/transactions',        [$transactionController, 'store']);
$router->add('GET',  '/transactions/{id}',   [$transactionController, 'show']);

$router->add('GET', '/', function () {
    echo json_encode(['message' => 'Welcome to the Crypto Tax API']);
});
$router->add('GET', '/health', function () {
    echo json_encode(['status' => 'ok']);
});

/*
|--------------------------------------------------------------------------
| Dispatch
|--------------------------------------------------------------------------
*/
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $path);

/*
|--------------------------------------------------------------------------
| Shutdown (fatal errors)
|--------------------------------------------------------------------------
*/
register_shutdown_function(function (): void {
    $error = error_get_last();

    if ($error !== null && $error['type'] === E_ERROR) {
        ErrorHandler::handleException(
            new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            )
        );
    }
});
