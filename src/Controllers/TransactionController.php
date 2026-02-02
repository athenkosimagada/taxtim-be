<?php

namespace App\Controllers;

use App\Gateways\TransactionGateway;
use App\Validators\TransactionRequestValidator;
use App\Exceptions\ValidationException;
use App\Core\Logger;
use PDOException;
use Exception;

class TransactionController
{
    protected TransactionGateway $gateway;
    protected TransactionRequestValidator $validator;

    public function __construct(
        TransactionGateway $gateway,
        TransactionRequestValidator $validator
    ) {
        $this->gateway   = $gateway;
        $this->validator = $validator;
    }

    public function index(): void
    {
        Logger::info('Listing transactions');

        $transactions = $this->gateway->getAll();

        $this->sendResponse(200, $transactions);
    }

    public function show(int $id): void
    {
        Logger::info("Fetching transaction id={$id}");

        $transaction = $this->gateway->getById($id);

        if (!$transaction) {
            Logger::warning("Transaction not found id={$id}");

            $this->sendResponse(404, [
                'error' => 'Transaction not found'
            ]);
            return;
        }

        $this->sendResponse(200, $transaction);
    }

    public function store(): void
    {
        Logger::info('Creating transaction');

        $data = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::warning('Invalid JSON payload');

            $this->sendResponse(400, [
                'error'   => 'Invalid JSON payload',
                'message' => $this->isProduction()
                    ? 'Malformed request body'
                    : json_last_error_msg(),
            ]);
            return;
        }

        try {
            $this->validator->validate($data);

            $id = $this->gateway->create($data);

            Logger::info("Transaction created id={$id}");

            $this->sendResponse(201, [
                'message' => 'Transaction created successfully',
                'id'      => $id,
            ]);
        } catch (ValidationException $ex) {
            Logger::warning('Validation failed');

            $this->sendResponse(422, [
                'error' => $ex->getMessage(),
            ]);
        }
    }

    public function remove(int $id): void
    {
        Logger::info("Deleting transaction id={$id}");

        $transaction = $this->gateway->getById($id);

        if (!$transaction) {
            Logger::warning("Transaction not found id={$id}");

            $this->sendResponse(404, [
                'error' => 'Transaction not found'
            ]);
            return;
        }

        try {
            $this->gateway->delete($id);

            Logger::info("Transaction deleted id={$id}");

            $this->sendResponse(200, [
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (PDOException $ex) {
            Logger::error("Database error deleting transaction id={$id}");

            $this->sendResponse(500, [
                'error' => 'Database error occurred'
            ]);
        } catch (Exception $ex) {
            Logger::error("Unexpected error deleting transaction id={$id}");

            $this->sendResponse(500, [
                'error' => 'An unexpected error occurred'
            ]);
        }
    }

    private function sendResponse(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    private function isProduction(): bool
    {
        return getenv('APP_ENV') === 'production';
    }
}
