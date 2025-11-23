<?php

namespace App\Enums;

enum DiscountTypeEnum: int{

    case NO_DISCOUNT = 0;
    case FIXED = 1;
    case PERCENTAGE = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
