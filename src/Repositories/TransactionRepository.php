<?php

namespace App\Repositories;

use App\Database\Database;
use App\Models\Transaction;
use PDO;

class TransactionRepository 
{
    public static function create(Transaction $tx): int
    {
        $db = Database::connection();
        $stmt = $db->prepare("
            INSERT INTO transactions (
                wallet, type, asset_from, asset_to,
                quantity, unit_price_zar, fee_zar,
                asset_from_market_price_zar, executed_at
            ) VALUES (
                :wallet, :type, :asset_from, :asset_to,
                :quantity, :unit_price_zar, :fee_zar,
                :asset_from_market_price_zar, :executed_at
            )
        ");

        $stmt->execute([
            ':wallet' => $tx->wallet,
            ':type' => $tx->type,
            ':asset_from' => $tx->assetFrom,
            ':asset_to' => $tx->assetTo,
            ':quantity' => $tx->quantity,
            ':unit_price_zar' => $tx->unitPriceZar,
            ':fee_zar' => $tx->feeZar,
            ':asset_from_market_price_zar' => $tx->assetFromMarketPriceZar,
            ':executed_at' => $tx->executedAt->format('Y-m-d H:i:s'),
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getAll(): array
    {
        $db = Database::connection();
        $stmt = $db->query("SELECT * FROM transactions ORDER BY executed_at ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $transactions = [];

        foreach ($rows as $row) {
            $tx = new Transaction();
            
            $tx->wallet = $row['wallet'];
            $tx->type = $row['type'];

            $tx->assetFrom = $row['asset_from'];
            $tx->assetTo = $row['asset_to'];

            $tx->quantity = (float)$row['quantity'];
            $tx->unitPriceZar = (float)$row['unit_price_zar'];
            $tx->feeZar = (float)$row['fee_zar'];

            $tx->assetFromMarketPriceZar = $row['asset_from_market_price_zar']
                ? (float)$row['asset_from_market_price_zar']
                : null;

            $tx->executedAt = new \DateTime($row['executed_at']);

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