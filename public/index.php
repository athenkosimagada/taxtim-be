<?php

require_once __DIR__ . '/autoload.php';

use App\Routing\Router;
use App\Controllers\TransactionController;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$router = new Router();

$router->post('/api/transactions/import', function () {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit();
    }

    $controller = new TransactionController();
    $controller->addTransactions($data);

    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Transactions created successfully']);
});

$router->get('/api/transactions', function () {
    $controller = new TransactionController();
    $result = $controller->getTransactions();

    http_response_code(200);
    echo json_encode($result, JSON_PRETTY_PRINT);
});

$router->get('/api/transactions/calculate', function () {
    $controller = new TransactionController();
    $result = $controller->getFifoCalculation();

    http_response_code(200);
    echo json_encode($result, JSON_PRETTY_PRINT);
});

$router->delete('/api/transactions', function () {
    $controller = new TransactionController();
    $controller->deleteAllTransactions();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'All transactions deleted successfully']);
});

$router->get('/api/reports/tax-year/{year}', function ($params) {
   $controller = new TransactionController();
   $result = $controller->getTaxYearReport($params);

   http_response_code(200);
   echo json_encode($result, JSON_PRETTY_PRINT);
});

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);