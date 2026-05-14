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

    public static function availableLocales(): array
    {
        return [
            self::ID->value => __('navigation.i18n.id'),
            self::EN->value => __('navigation.i18n.en'),
            self::FR->value => __('navigation.i18n.fr'),
        ];
    }
}
