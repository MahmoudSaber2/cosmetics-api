<?php

namespace App\Enums;

enum OrderStatusEnum: int{

    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;
    case COMPLETED = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
