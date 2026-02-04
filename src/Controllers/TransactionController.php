<?php

namespace App\Controllers;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Services\TransactionService;

class TransactionController
{
    public function addTransactions(array $transactions): void
    {
        TransactionService::createTransactions($transactions);
    }

    public function getTransactions(): array
    {
        return TransactionService::getAllTransactions();
    }

    public function getFifoCalculation(): array
    {
        $transactions = TransactionService::getAllTransactions();
        return TransactionService::calculateFIFO($transactions);
    }

    public function deleteAllTransactions(): void
    {
        TransactionService::deleteAllTransactions();
    }
}