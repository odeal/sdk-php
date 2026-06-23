<?php

declare(strict_types=1);

namespace Odeal\Sdk\Enums;

/**
 * 
 */
enum PaymentOptionType: string
{
    /**
     * API Değeri: CASH
     */
    case CASH = 'CASH';
    /**
     * API Değeri: CREDITCARD
     */
    case CREDITCARD = 'CREDITCARD';
    /**
     * API Değeri: GIFT
     */
    case GIFT = 'GIFT';
    /**
     * API Değeri: OPEN_ACCOUNT
     */
    case OPENACCOUNT = 'OPEN_ACCOUNT';
    /**
     * API Değeri: FOOD_CARD
     */
    case FOODCARD = 'FOOD_CARD';

    /**
     * Tüm değerleri array olarak döner.
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}