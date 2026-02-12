<?php

namespace App\Enums;

enum TransactionType: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';
    case TRADE = 'TRADE';
    case TRANSFER = 'TRANSFER';
}