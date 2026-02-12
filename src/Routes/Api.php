<?php

namespace App\Routes;

use App\Routing\Router;
use App\Controllers\AuthController;
use App\Controllers\TransactionController;

$router = new Router();

$router->post('/api/register', [AuthController::class, 'register']);
$router->post('/api/login', [AuthController::class, 'login']);

$router->get('/api/transactions', [TransactionController::class, 'getTransactions']);
$router->post('/api/transactions', [TransactionController::class, 'addTransactions']);
$router->get('/api/transactions/calculate', [TransactionController::class, 'getFifoCalculation']);
$router->delete('/api/transactions', [TransactionController::class, 'deleteAllTransactions']);

$router->get('/api/reports/tax-year/{year}', [TransactionController::class, 'getTaxYearReport']);

return $router;