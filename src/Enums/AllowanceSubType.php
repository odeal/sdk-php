<?php

declare(strict_types=1);

namespace Odeal\Sdk\Enums;

/**
 * İskonto tipi: LINE satır (ürün) iskontosu, SUB ara toplam iskontosunun satıra düşen payı.
 */
enum AllowanceSubType: string
{
    /**
     * API Değeri: LINE
     */
    case LINE = 'LINE';
    /**
     * API Değeri: SUB
     */
    case SUB = 'SUB';

    /**
     * Tüm değerleri array olarak döner.
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}