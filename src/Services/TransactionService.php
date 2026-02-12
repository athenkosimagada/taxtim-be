<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Utils\FifoHelper;
use App\Enums\TransactionType;


class TransactionService
{
    public static function  createTransactions(array $transactions): void
    {
        foreach ($transactions as $data) {
            $tx = new Transaction();
            
            $tx->date = new \DateTime($data['date']);
            $tx->type = TransactionType::from(strtoupper($data['type']));

            $tx->sellCoin = $data['sellCoin'];
            $tx->sellAmount = $data['sellAmount'];

            $tx->buyCoin = $data['buyCoin'];
            $tx->buyAmount = $data['buyAmount'];

            $tx->pricePerCoin = $data['pricePerCoin'];
            $tx->fiatCurrency = $data['fiatCurrency'] ?? 'ZAR';

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
        $rows = [];
        $baseCostSnapshots = [];

        usort($transactions, fn ($a, $b) => $a->date <=> $b->date);
        
        foreach ($transactions as $tx) {
            $taxYear = FifoHelper::getTaxYear($tx->date);

            switch ($tx->type) {
                case TransactionType::BUY:
                    $balances[$tx->buyCoin][] = [
                        'qty' => (float)$tx->buyAmount,
                        'price' => (float)$tx->pricePerCoin,
                        'date' => $tx->date->format('Y-m-d')
                    ];

                    $rows[] = FifoHelper::formatRow($tx, 0, 0, []);
                    break;
                case TransactionType::SELL:
                    $proceeds = (float)$tx->sellAmount * (float)$tx->pricePerCoin;

                    $fifo = FifoHelper::fifoSell(
                        $tx->sellCoin,
                        $tx->sellAmount,
                        $balances
                    );

                    $gain = $proceeds - $fifo['cost'];

                    $capitalGains[$taxYear][$tx->sellCoin] =
                        ($capitalGains[$taxYear][$tx->sellCoin] ?? 0) + $gain;

                    $rows[] = FifoHelper::formatRow($tx, $proceeds, $gain, $fifo['lots']);
                    break;

                case TransactionType::TRADE:
                    $proceeds = (float)$tx->buyAmount * (float)$tx->pricePerCoin;

                    $fifo = FifoHelper::fifoSell(
                        $tx->sellCoin,
                        $tx->sellAmount,
                        $balances
                    );

                    $gain = $proceeds - $fifo['cost'];

                    $capitalGains[$taxYear][$tx->sellCoin] =
                        ($capitalGains[$taxYear][$tx->sellCoin] ?? 0) + $gain;

                    $balances[$tx->buyCoin][] = [
                        'qty' => (float)$tx->buyAmount,
                        'price' => (float)$tx->pricePerCoin,
                        'date' => $tx->date->format('Y-m-d')
                    ];

                    $rows[] = FifoHelper::formatRow($tx, $proceeds, $gain, $fifo['lots']);
                    break;
            }

            $baseCostSnapshots[$taxYear] = FifoHelper::calculateBaseCosts($balances);
        }

        return [
            'balances' => FifoHelper::formatBalances($balances),
            'calculations' => $rows,
            'capitalGains' => $capitalGains,
            'baseCostSnapshots' => $baseCostSnapshots
        ];
    }
}