<?php

class TransactionRequestValidator
{
    public function validate(array $data): void
    {
        $requiredFields = ['type', 'coin', 'amount', 'price'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new ValidationException("Field '{$field}' is required");
            }
        }
        if (!in_array($data['type'], ['buy', 'sell'], true)) {
            throw new ValidationException("Field 'type' must be either 'buy' or 'sell'");
        }
        
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new ValidationException("Field 'amount' must be a positive number");
        }

        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            throw new ValidationException("Field 'price' must be a positive number");
        }
    }
}