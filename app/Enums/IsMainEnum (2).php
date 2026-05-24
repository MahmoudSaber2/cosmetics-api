<?php

namespace App\Enums;

enum IsMainEnum: int
{
    case NOT_MAIN = 0;
    case MAIN = 1;



    public function toBool(): bool
    {
        return $this === self::MAIN;
    }

    public static function fromBool(bool $value): self
    {
        return $value ? self::MAIN : self::NOT_MAIN;
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
