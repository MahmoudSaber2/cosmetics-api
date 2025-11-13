<?php

namespace App\Enums\Otp;

enum OtpDeliveryMethod: int
{
    case EMAIL = 1;
    case SMS = 2;
    case WHATSAPP = 3;
    case PUSH_NOTIFICATION = 4;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::WHATSAPP => 'WhatsApp',
            self::PUSH_NOTIFICATION => 'Push Notification',
        };
    }

    public function getProviderClass(): string
    {
        return match($this) {
            self::EMAIL => \App\Services\Otp\EmailOtpProvider::class,
        };
    }
}
