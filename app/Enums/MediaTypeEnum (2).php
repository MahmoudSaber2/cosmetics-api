<?php

namespace App\Enums;

enum MediaTypeEnum: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case FILE = 'file';



    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
