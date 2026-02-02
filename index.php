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
        $base . 'Helpers/',
        $base . 'Middleware/',
        $base . 'Services/',
        $base . 'Exceptions/',
        $base . 'Validators/',
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

// ==================
// CORS
// ==================
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
*/
$database = new Database(
    getenv('MYSQLHOST') ?: getenv('MYSQL_HOST'),
    getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE'),
    getenv('MYSQLUSER') ?: getenv('MYSQL_USER'),
    getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD')
);

function initializeDatabase(PDO $pdo, string $schemaFile, string $seedFile): void {
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    if (count($tables) === 0) {
        $schemaSql = file_get_contents($schemaFile);
        $pdo->exec($schemaSql);

        $seedSql = file_get_contents($seedFile);
        $pdo->exec($seedSql);

        error_log("Database initialized (schema + seed).");
    }
}

$pdo = $database->getConnection();

initializeDatabase(
    $database->getConnection(),
    __DIR__ . '/database/schema.sql',
    __DIR__ . '/database/seed.sql'
);

$transactionGateway    = new TransactionGateway($database);
$transactionRequestValidator = new TransactionRequestValidator();
$transactionController = new TransactionController($transactionGateway, $transactionRequestValidator);

/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/
$router = new Router();

$router->add('GET',  '/transactions',        [$transactionController, 'index']);
$router->add('POST', '/transactions',        [$transactionController, 'store']);
$router->add('GET',  '/transactions/{id:\d+}',   [$transactionController, 'show']);
$router->add('DELETE',  '/transactions/{id:\d+}',   [$transactionController, 'remove']);

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
