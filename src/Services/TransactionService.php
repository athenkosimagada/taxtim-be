<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Utils\FifoHelper;


class TransactionService
{
    public static function  createTransactions(array $transactions): void
    {
        foreach ($transactions as $data) {
            $tx = new Transaction();
            
            $tx->wallet = $data['wallet'];
            $tx->type = $data['type'];

            $tx->assetFrom = $data['assetFrom'] ?? null;
            $tx->assetTo = $data['assetTo'] ?? null;

            $tx->quantity = (float)($data['quantity']);
            $tx->unitPriceZar = (float)($data['unitPriceZar']);
            $tx->feeZar = (float)($data['feeZar'] ?? 0);

            $tx->assetFromMarketPriceZar =
                $data['assetFromMarketPriceZar'] ?? null;

            $tx->executedAt = new \DateTime($data['executedAt']);

            TransactionRepository::create($tx);
        }
    }

    public static function getAllTransactions(): array
    {
        return TransactionRepository::getAll();
    }

    public static function deleteAllTransactions(): void
    {
        TransactionRepository::deleteAll();
    }

    public static function calculateFIFO(array $transactions): array
    {
        $balances = []; 
        $capitalGains = [];

        usort($transactions, fn ($a, $b) =>
            $a->executedAt <=> $b->executedAt
        );
        
        foreach ($transactions as $tx) {
            $taxYear = FifoHelper::getTaxYear($tx->executedAt);

            switch ($tx->type) {
                case 'BUY':
                    $balances[$tx->assetTo][] = [
                        'quantity' => $tx->quantity,
                        'unitPriceZar' => $tx->unitPriceZar,
                        'date' => $tx->executedAt->format('Y-m-d'),
                    ];
                    break;
                case 'SELL':
                    self::fifoDispose(
                        $tx->assetFrom,
                        $tx->quantity,
                        $tx->unitPriceZar,
                        $tx->executedAt,
                        $balances,
                        $capitalGains,
                        $taxYear
                    );
                    break;

                case 'TRADE':
                    if ($tx->assetFromMarketPriceZar <= 0) {
                        throw new \Exception('assetFromMarketPriceZar is required for TRADE');
                    }

                    $zarRequired = $tx->quantity * $tx->unitPriceZar;
                    $btcSoldQty = $zarRequired / $tx->assetFromMarketPriceZar;

                    $costSold = self::fifoSell(
                        $tx->assetFrom,
                        $btcSoldQty,
                        $balances
                    );

                    $capitalGains[$taxYear] = ($capitalGains[$taxYear] ?? 0)
                        + ($zarRequired - $costSold);

                    $balances[$tx->assetTo][] = [
                        'quantity' => $tx->quantity,
                        'unitPriceZar' => $tx->unitPriceZar,
                        'date' => $tx->executedAt->format('Y-m-d'),
                    ];
                    break;
            }
        }

        return [
            'balances' => $balances,
            'capitalGains' => $capitalGains,
            'baseCosts' => self::calculateBaseCosts($balances),
        ];
    }

    private static function fifoSell(string $asset, float $qty, array &$balances): float
    {
        if (!isset($balances[$asset])) {
            throw new \Exception("No balance for $asset");
        }

        $cost = 0;

        while ($qty > 0) {
            $lot = &$balances[$asset][0];

            if ($lot['quantity'] <= $qty) {
                $cost += $lot['quantity'] * $lot['unitPriceZar'];
                $qty -= $lot['quantity'];
                array_shift($balances[$asset]);
            } else {
                $cost += $qty * $lot['unitPriceZar'];
                $lot['quantity'] -= $qty;
                $qty = 0;
            }
        }

        return $cost;
    }

    private static function fifoDispose(
        string $asset,
        float $qty,
        float $price,
        \DateTime $date,
        array &$balances,
        array &$capitalGains,
        string $year
    ): void {
        $cost = self::fifoSell($asset, $qty, $balances);
        $proceeds = $qty * $price;
        $capitalGains[$year] = ($capitalGains[$year] ?? 0) + ($proceeds - $cost);
    }

    private static function calculateBaseCosts(array $balances): array
    {
        $out = [];

        foreach ($balances as $asset => $lots) {
            $qty = 0;
            $cost = 0;

            foreach ($lots as $lot) {
                $qty += $lot['quantity'];
                $cost += $lot['quantity'] * $lot['unitPriceZar'];
            }

            $out[$asset] = [
                'quantity' => round($qty, 8),
                'cost' => round($cost, 2),
            ];
        }

        return $out;
    }
}