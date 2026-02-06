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
            
            $tx->wallet = $data['wallet'] ?? 'default';
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

    public static function getTaxYearReport(int $year): array
    {
        $transactions = TransactionRepository::getAll();
        $fifo = self::calculateFIFO($transactions);

        $disposals = array_values(array_filter(
            $fifo['calculations'],
            fn ($row) => $row['taxYear'] === $year
        ));

        return [
            'taxYear' => $year,
            'capitalGains' => $fifo['capitalGains'][$year] ?? [
                'TOTAL' => 0
            ],
            'disposals' => $disposals,
            'openingBaseCosts' => $fifo['baseCostSnapshots'][$year - 1] ?? [],
            'closingBaseCosts' => $fifo['baseCostSnapshots'][$year] ?? [],
        ];
    }

    public static function calculateFIFO(array $transactions): array
    {
        $balances = []; 
        $capitalGains = [];
        $calculationRows = [];

        $baseCostSnapshots = [];
        $currentTaxYear = null;

        usort($transactions, fn ($a, $b) =>
            $a->executedAt <=> $b->executedAt
        );
        
        foreach ($transactions as $tx) {
            $taxYear = FifoHelper::getTaxYear($tx->executedAt);

            if ($currentTaxYear !== null && $taxYear !== $currentTaxYear) {
                $baseCostSnapshots[$currentTaxYear] =
                    self::calculateBaseCosts($balances);
            }

            $currentTaxYear = $taxYear;

            switch ($tx->type) {
                case 'BUY':
                    $balances[$tx->assetTo][] = [
                        'quantity' => $tx->quantity,
                        'unitPriceZar' => $tx->unitPriceZar,
                        'date' => $tx->executedAt->format('Y-m-d'),
                    ];
                    break;
                case 'SELL':
                    $result = self::fifoSell(
                        $tx->assetFrom,
                        $tx->quantity,
                        $balances
                    );

                    $proceeds = $tx->quantity * $tx->unitPriceZar;
                    $gain = $proceeds - $result['cost'];

                    $capitalGains[$taxYear][$tx->assetFrom] =
                        ($capitalGains[$taxYear][$tx->assetFrom] ?? 0) + $gain;

                    $calculationRows[] = [
                        'type' => 'SELL',
                        'asset' => $tx->assetFrom,
                        'date' => $tx->executedAt->format('Y-m-d'),
                        'quantity' => $tx->quantity,
                        'proceeds' => round($proceeds, 2),
                        'cost' => $result['cost'],
                        'gain' => round($gain, 2),
                        'lots' => $result['lots'],
                        'taxYear' => $taxYear,
                    ];
                    break;

                case 'TRADE':
                    if ($tx->assetFromMarketPriceZar <= 0) {
                        throw new \Exception('assetFromMarketPriceZar is required for TRADE');
                    }

                    $zarProceeds = $tx->quantity * $tx->unitPriceZar;
                    $soldQty = $zarProceeds / $tx->assetFromMarketPriceZar;

                    $result = self::fifoSell(
                        $tx->assetFrom,
                        $soldQty,
                        $balances
                    );

                    $gain = $zarProceeds - $result['cost'];

                    $capitalGains[$taxYear][$tx->assetFrom] =
                        ($capitalGains[$taxYear][$tx->assetFrom] ?? 0) + $gain;

                    $balances[$tx->assetTo][] = [
                        'quantity' => $tx->quantity,
                        'unitPriceZar' => $tx->unitPriceZar,
                        'date' => $tx->executedAt->format('Y-m-d'),
                    ];

                    $calculationRows[] = [
                        'type' => 'TRADE',
                        'from' => $tx->assetFrom,
                        'to' => $tx->assetTo,
                        'date' => $tx->executedAt->format('Y-m-d'),
                        'soldQuantity' => round($soldQty, 8),
                        'proceeds' => round($zarProceeds, 2),
                        'cost' => $result['cost'],
                        'gain' => round($gain, 2),
                        'lots' => $result['lots'],
                        'taxYear' => $taxYear,
                    ];
                    break;
            }
        }

        if ($currentTaxYear !== null) {
            $baseCostSnapshots[$currentTaxYear] =
                self::calculateBaseCosts($balances);
        }

        foreach ($capitalGains as $year => $assets) {
            $total = '0';

            foreach ($assets as $asset => $gain) {
                if ($asset === 'TOTAL') continue;

                $capitalGains[$year][$asset] = round($gain, 2);
                $total = \bcadd($total, (string)$gain, 8);
            }

            $capitalGains[$year]['TOTAL'] = round((float)$total, 2);
        }

        return [
            'transactions' => $transactions,
            'calculations' => $calculationRows,
            'balances' => $balances,
            'baseCosts' => self::calculateBaseCosts($balances),
            'baseCostSnapshots' => $baseCostSnapshots,
            'capitalGains' => $capitalGains,
        ];
    }

    private static function fifoSell(string $asset, float $qty, array &$balances): array
    {
        if (!isset($balances[$asset])) {
            throw new \Exception("No balance for $asset");
        }

        $cost = '0';
        $lotsUsed = [];

        while ($qty > 0) {
            $lot = &$balances[$asset][0];

            $usedQty = min($qty, $lot['quantity']);
            $usedCost = \bcmul((string)$usedQty, (string)$lot['unitPriceZar'], 8);

            $lotsUsed[] = [
                'asset' => $asset,
                'quantity' => round($usedQty, 8),
                'unitPriceZar' => $lot['unitPriceZar'],
                'date' => $lot['date'],
                'cost' => round((float)$usedCost, 2),
            ];

            $cost = \bcadd($cost, $usedCost, 8);
            
            $lot['quantity'] -= $usedQty;
            $qty -= $usedQty;

            if ($lot['quantity'] <= 0) {
                array_shift($balances[$asset]);
            }
        }

        return [
            'cost' => round((float)$cost, 2),
            'rawCost' => $cost,
            'lots' => $lotsUsed
        ];
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