<?php

namespace App\Repositories;

use App\Database\Database;
use App\Models\Transaction;
use App\Enums\TransactionType;
use PDO;

class TransactionRepository 
{
    public static function create(Transaction $tx): int
    {
        $db = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO transactions (
                date, 
                type,
                sell_coin,
                sell_amount,
                buy_coin,
                buy_amount,
                price_per_coin,
                fiat_currency
            ) VALUES (
                :date,
                :type,
                :sell_coin,
                :sell_amount,
                :buy_coin,
                :buy_amount,
                :price_per_coin,
                :fiat_currency
            )
        ");

        $stmt->execute([
            ':date' => $tx->date->format('Y-m-d'),
            ':type' => $tx->type->value,
            ':sell_coin' => $tx->sellCoin,
            ':sell_amount' => $tx->sellAmount,
            ':buy_coin' => $tx->buyCoin,
            ':buy_amount' => $tx->buyAmount,
            ':price_per_coin' => $tx->pricePerCoin,
            ':fiat_currency' => $tx->fiatCurrency
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getAll(): array
    {
        $db = Database::connection();
        $stmt = $db->query("SELECT * FROM transactions ORDER BY date ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $transactions = [];

        foreach ($rows as $row) {
            $tx = new Transaction();

            $tx->date = new \DateTime($row['date']);
            
            $tx->type = TransactionType::from($row['type']);;

            $tx->sellCoin = $row['sell_coin'];
            $tx->sellAmount = $row['sell_amount'];

            $tx->buyCoin = $row['buy_coin'];
            $tx->buyAmount = $row['buy_amount'];

            $tx->pricePerCoin = $row['price_per_coin'];
            $tx->fiatCurrency = $row['fiat_currency'];

            $transactions[] = $tx;
        }

        return $transactions;
    }

    public static function deleteAll(): void
    {
        $db = Database::connection();
        $stmt = $db->prepare("DELETE FROM transactions");
        $stmt->execute();
    }
}