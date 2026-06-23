<?php

declare(strict_types=1);

namespace Odeal\Sdk\Enums;

/**
 * Koşullu. Eğer ödeme tipi 'FOOD_CARD' ise ZORUNLUDUR.
    /// - 100001: Multinet
    /// - 100002: Setcard
    /// - 100003: Edenred
    /// - 100004: Tokenflex
    /// - 100005: Pluxee
    /// - 100006: Metropol
    /// - 100007: Paye
 */
enum ReceiptInfoFoodCardBrandId: int
{
    /**
     * API Değeri: 100001
     */
    case MULTINET = 100001;
    /**
     * API Değeri: 100002
     */
    case SETCARD = 100002;
    /**
     * API Değeri: 100003
     */
    case EDENRED = 100003;
    /**
     * API Değeri: 100004
     */
    case TOKENFLEX = 100004;
    /**
     * API Değeri: 100005
     */
    case PLUXEE = 100005;
    /**
     * API Değeri: 100006
     */
    case METROPOL = 100006;
    /**
     * API Değeri: 100007
     */
    case PAYE = 100007;

    /**
     * Tüm değerleri array olarak döner.
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}