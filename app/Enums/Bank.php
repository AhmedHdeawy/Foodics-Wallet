<?php

namespace App\Enums;

enum Bank: string
{
    case FOODICS = 'foodics';
    case ACME = 'acme';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
