<?php

class TransactionController
{
    public function __construct(private TransactionGateway $gateway) {}

    public function index(): void
    {
        echo json_encode($this->gateway->getAll());
    }

    public function show(int $id): void
    {
        $transaction = $this->gateway->getById($id);

        if (!$transaction) {
            http_response_code(404);
            echo json_encode(['error' => 'Transaction not found']);
            return;
        }

        echo json_encode($transaction);
    }

    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        $id = $this->gateway->create($data);

        http_response_code(201);
        echo json_encode(['id' => $id]);
    }
}
