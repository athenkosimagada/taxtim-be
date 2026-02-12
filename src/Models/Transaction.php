<?php

namespace App\Models;

use DateTime;
use App\Enums\TransactionType;
use InvalidArgumentException;

class Transaction
{
    public int $id;

    public DateTime $date;
    public TransactionType $type;

    public string $sellCoin;
    public float $sellAmount;

    public string $buyCoin;
    public float $buyAmount;

    public float $pricePerCoin;
    public string $fiatCurrency = 'ZAR';

    public static function validate(array $data): bool
    {
        $required = [
            'date',
            'type',
            'sellCoin',
            'sellAmount',
            'buyCoin',
            'buyAmount',
            'pricePerCoin',
        ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new InvalidArgumentException("Missing or empty field: $field");
            }
        }

        if (!DateTime::createFromFormat('Y-m-d', $data['date'])) {
            throw new InvalidArgumentException("Invalid date format. Use YYYY-MM-DD.");
        }

        $type = TransactionType::tryFrom(strtoupper($data['type']));
        if (!$type) {
            throw new InvalidArgumentException("Invalid transaction type: {$data['type']}");
        }

        foreach (['sellAmount', 'buyAmount', 'pricePerCoin'] as $field) {
            if (!is_numeric($data[$field]) || (float)$data[$field] <= 0) {
                throw new InvalidArgumentException("$field must be a positive number.");
            }
        }

        match ($type) {
            TransactionType::BUY =>
                self::assert($data['sellCoin'] === 'ZAR', 'BUY must sell ZAR'),

            TransactionType::SELL =>
                self::assert($data['buyCoin'] === 'ZAR', 'SELL must buy ZAR'),

            TransactionType::TRADE =>
                self::assert(
                    $data['sellCoin'] !== 'ZAR' && $data['buyCoin'] !== 'ZAR',
                    'TRADE cannot involve ZAR'
                ),
        };

        return true;
    }

    private static function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new InvalidArgumentException($message);
        }
    }
}
