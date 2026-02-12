<?php

namespace App\Utils;

use APP\Models\Transaction;

class FifoHelper
{
    public static function getTaxYear(\DateTime $date): int
    {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');

        return ($month >= 3) ? $year + 1 : $year;
    }

    public static function formatBalances(array $balances): array
    {
        $result = [];
        foreach ($balances as $coin => $lots) {
            $totalQty = array_sum(array_column($lots, 'qty'));
            $result[$coin] = [
                'totalQty' => round($totalQty, 8),
                'lots' => array_map(fn($lot) => [
                    'qty' => round($lot['qty'], 8),
                    'price' => $lot['price'],
                    'date' => $lot['date'],
                ], $lots),
            ];
        }
        return $result;
    }

    public static function fifoSell(string $asset, float $qty, array &$balances): array
    {
        if (!isset($balances[$asset]) || empty($balances[$asset])) {
            throw new \Exception("Cannot sell $asset: no balance available. You must buy before selling.");
        }

        $remaining = $qty;
        $cost = 0.0;
        $lotsUsed = [];

        $tolerance = 0.00000001;

        while ($remaining > $tolerance) {
            if (empty($balances[$asset])) {
                throw new \Exception("Insufficient $asset balance to sell $qty");
            }

            $lot = &$balances[$asset][0];
            $availableQty = $lot['qty'];

            $usedQty = min($remaining, $availableQty);
            $usedCost = $usedQty * $lot['price'];

            $lotsUsed[] = [
                'asset' => $asset,
                'qty' => round($usedQty, 8),
                'price' => $lot['price'],
                'date' => $lot['date'],
                'cost' => round($usedCost, 2)
            ];

            $cost += $usedCost;

            $lot['qty'] -= $usedQty;
            $remaining -= $usedQty;

            if ($lot['qty'] <= $tolerance) {
                array_shift($balances[$asset]);
            }
        }

        return [
            'cost' => round($cost, 2),
            'lots' => $lotsUsed
        ];
    }

    public static function calculateBaseCosts(array $balances): array
    {
        $baseCosts = [];

        foreach ($balances as $coin => $lots) {
            $baseCosts[$coin] = round(
                array_reduce(
                    $lots,
                    fn($sum, $lot) => $sum + ($lot['qty'] * $lot['price']),
                    0
                ),
                2
            );
        }

        return $baseCosts;
    }

    public static function formatRow(
        Transaction $tx,
        float $proceeds,
        float $gain,
        array $lots
    ): array {
        return [
            'date' => $tx->date->format('Y-m-d'),
            'type' => $tx->type->value,
            'sellCoin' => $tx->sellCoin,
            'sellAmount' => $tx->sellAmount,
            'buyCoin' => $tx->buyCoin,
            'buyAmount' => $tx->buyAmount,
            'pricePerCoin' => $tx->pricePerCoin,
            'calculations' => [
                'proceeds' => round($proceeds, 2),
                'costBase' => round($proceeds - $gain, 2),
                'capitalGain' => round($gain, 2),
                'fifoLots' => $lots
            ]
        ];
    }
}
