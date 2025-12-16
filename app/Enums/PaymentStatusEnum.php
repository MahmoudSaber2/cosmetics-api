<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'في الانتظار',
            self::PROCESSING => 'قيد المعالجة',
            self::SUCCEEDED => 'نجح',
            self::FAILED => 'فشل',
            self::CANCELED => 'ملغي',
            self::REFUNDED => 'مسترد',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
