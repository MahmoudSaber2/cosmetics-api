<?php

namespace App\Enums;

enum OrderPaidEnum: int
{
    case UNPAID = 0;
    case PAID = 1;

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'غير مدفوع',
            self::PAID => 'مدفوع',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
