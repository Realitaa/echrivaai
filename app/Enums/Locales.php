<?php

namespace App\Enums;

enum Locales: string
{
    case ID = 'id';
    case EN = 'en';
    case FR = 'fr';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
