<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case CASH = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
            self::CASH => 'نقدي',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
