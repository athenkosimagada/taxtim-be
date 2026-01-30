<?php

class TransactionController
{
    public function __construct(private TransactionGateway $gateway) {}
    
     public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            
            $this->processResourceRequest($method, $id);
            
        } else {
            
            $this->processCollectionRequest($method);
            
        }
    }

    private function processResourceRequest(string $method): void
    {
        
    }

    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case 'GET':
                echo json_encode($this->gateway->getAll() ?? []);
                break;
            case 'POST':
                $data = (array) json_decode(file_get_contents('php://input'), true);

                $errors = [];

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data);
                http_response_code(201);

                echo json_encode([
                    "message" => "Transaction created",
                    "id" => $id
                    ]);
                break;
            default:
                http_response_code(405);
                header("Allow: GET, POST");
                break;
        }
    }
}