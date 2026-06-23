<?php

declare(strict_types=1);

namespace Odeal\Sdk\Enums;

/**
 * Müşteri Tipi. Default: INDIVIDUAL.
 */
enum CustomerType: string
{
    /**
     * API Değeri: INDIVIDUAL
     */
    case INDIVIDUAL = 'INDIVIDUAL';
    /**
     * API Değeri: CORPORATE
     */
    case CORPORATE = 'CORPORATE';

    /**
     * Tüm değerleri array olarak döner.
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}