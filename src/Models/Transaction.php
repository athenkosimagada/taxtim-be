<?php

namespace App\Models;

use DateTime;

class Transaction
{
    public int $id;

    public string $wallet;
    public string $type;

    public ?string $assetFrom;
    public ?string $assetTo;

    public float $quantity;
    public float $unitPriceZar;
    public float $feeZar;

    public ?float $assetFromMarketPriceZar = null;

    public DateTime $executedAt;
}