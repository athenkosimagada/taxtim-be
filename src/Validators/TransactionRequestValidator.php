<?php

class TransactionRequestValidator
{
    private array $labels = [
        'type' => 'Type',
        'coin' => 'Coin',
        'amount' => 'Amount',
        'price' => 'Price',
    ];

    public function validate(array $data): void
    {
        $this->validateRequiredFields($data);
        $this->validateNoExtraFields($data);

        $this->validateType($data['type']);
        $this->validateCoin($data['coin']);
        $this->validateAmount($data['amount']);
        $this->validatePrice($data['price']);
    }

    private function validateRequiredFields(array $data): void
    {
        foreach ($this->labels as $field => $label) {
            if (!array_key_exists($field, $data) || $data[$field] === '') {
                throw new ValidationException("{$label} is required");
            }
        }
    }

    private function validateNoExtraFields(array $data): void
    {
        $allowedFields = array_keys($this->labels);

        foreach ($data as $field => $_) {
            if (!in_array($field, $allowedFields, true)) {
                $fieldName = ucfirst($field);
                throw new ValidationException("Unexpected field '{$fieldName}'");
            }
        }
    }

    private function validateType(string $type): void
    {
        if (!in_array($type, ['BUY', 'SELL', 'TRADE'], true)) {
            throw new ValidationException("Type must be either 'BUY', 'SELL', or 'TRADE' (uppercase)");
        }
    }

    private function validateCoin(string $coin): void
    {
        $coin = trim($coin);

        if ($coin === '' || strlen($coin) > 10) {
            throw new ValidationException("Coin must be between 1 and 10 characters");
        }
    }

    private function validateAmount($amount): void
    {
        if (!is_numeric($amount) || $amount <= 0) {
            throw new ValidationException("Amount must be a positive number");
        }
    }

    private function validatePrice($price): void
    {
        if (!is_numeric($price) || $price <= 0) {
            throw new ValidationException("Price must be a positive number");
        }
    }
}