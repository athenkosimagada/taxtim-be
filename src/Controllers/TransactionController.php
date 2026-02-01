<?php

class TransactionController
{
    protected $gateway;
    protected $validator;
    public function __construct($gateway, TransactionRequestValidator $validator) 
    {
        $this->gateway = $gateway;
        $this->validator = $validator;
    }

    public function index(): void
    {
        $transactions = $this->gateway->getAll();
        $this->sendResponse(200, $transactions);
    }

    public function show(int $id): void
    {
        $transaction = $this->gateway->getById($id);

        if (!$transaction) {
            $this->sendResponse(404, ['error' => 'Transaction not found']);
            return;
        }

        $this->sendResponse(200, $transaction);
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $this->sendResponse(400, ['error' => 'Invalid JSON payload']);
            return;
        }

        try {
            $this->validator->validate($data);
            $id = $this->gateway->create($data);
            $this->sendResponse(201, ['id' => $id]);
        } catch (ValidationException $ex) {
            $this->sendResponse(422, ['error' => $ex->getMessage()]);
        }
    }

    public function remove(int $id): void
    {
        $transaction = $this->gateway->getById($id);

        if (!$transaction) {
            $this->sendResponse(404, ['error' => 'Transaction not found']);
            return;
        }

        try {
            $this->gateway->delete($id);
            $this->sendResponse(200, ['message' => 'Transaction deleted successfully']);
        } catch (Exception $ex) {
            $this->sendResponse(500, ['error' => 'Failed to delete transaction']);
        }
    }

    private function sendResponse(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES);
    }
}
