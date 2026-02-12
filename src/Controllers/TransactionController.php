<?php

namespace App\Controllers;

use App\Services\TransactionService;
use App\Models\Transaction;
use App\Middleware\AuthMiddleware;

class TransactionController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function addTransactions(): void
    {
        $rawInput = file_get_contents('php://input');
        $transactions = json_decode($rawInput, true);

        if (!is_array($transactions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON array']);
            return;
        }

        try {
            foreach ($transactions as $data) {
                Transaction::validate($data);
            }

            TransactionService::createTransactions($transactions);

            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Transactions validated and created']);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
    }

    public function getTransactions(): void
    {
        $transactions = TransactionService::getAllTransactions();
        
        http_response_code(200);
        echo json_encode($transactions, JSON_PRETTY_PRINT);
    }

    public function getFifoCalculation(): void
    {
        $transactions = TransactionService::getAllTransactions();
        $fifoCalculation = TransactionService::calculateFIFO($transactions);

        http_response_code(200);
        echo json_encode($fifoCalculation, JSON_PRETTY_PRINT);
    }

    public function getTaxYearReport(array $params)
    {
        $year = (int) $params['year'];

        if ($year < 2000 || $year > 2100) {
            http_response_code(400);
            return ['error' => 'Invalid tax year'];
        }

        $report = TransactionService::getTaxYearReport($year);

        http_response_code(200);
        echo json_encode($report, JSON_PRETTY_PRINT);
    }

    public function deleteAllTransactions(): void
    {
        TransactionService::deleteAllTransactions();

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'All transactions deleted successfully']);
    }
}