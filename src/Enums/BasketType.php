<?php

declare(strict_types=1);

namespace Odeal\Sdk\Enums;

/**
 * Global Basket Type Enum.
 */
enum BasketType: string
{
    /**
     * API Değeri: SIMPLE
     */
    case SIMPLE = 'SIMPLE';
    /**
     * API Değeri: ADVANCE
     */
    case ADVANCE = 'ADVANCE';
    /**
     * API Değeri: CURRENT_ACCOUNT
     */
    case CURRENTACCOUNT = 'CURRENT_ACCOUNT';

    /**
     * Tüm değerleri array olarak döner.
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}