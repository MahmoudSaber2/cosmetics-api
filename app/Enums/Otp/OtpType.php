<?php

namespace App\Enums\Otp;

enum OtpType: int{

    case REGISTER = 0;
    case FORGET_PASSWORD = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::REGISTER => 'Account Registration',
            self::FORGET_PASSWORD => 'Password Reset',
        };
    }

    public function getExpirationMinutes(): int
    {
        return match($this) {
            self::REGISTER => 5,
            self::FORGET_PASSWORD => 5,
        };
    }
}
