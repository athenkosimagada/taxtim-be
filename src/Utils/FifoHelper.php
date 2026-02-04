<?php

namespace App\Utils;

class FifoHelper
{
    public static function getTaxYear(\DateTime $date): int
    {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');

        return ($month >= 3) ? $year + 1 : $year;
    }
}
