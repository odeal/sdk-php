<?php

declare(strict_types=1);

namespace Odeal\Sdk\Enums;

/**
 * Terminalin entegrasyon profil tipini belirtir.
    /// - INTERNAL_BASKET
    /// - EXTERNAL_DEVICE_TO_DEVICE
    /// - EXTERNAL_BASKET_WITH_APP (APP2APP)
 */
enum ProfileType: string
{
    /**
     * API Değeri: INTERNAL_BASKET
     */
    case INTERNALBASKET = 'INTERNAL_BASKET';
    /**
     * API Değeri: EXTERNAL_DEVICE_TO_DEVICE
     */
    case EXTERNALDEVICETODEVICE = 'EXTERNAL_DEVICE_TO_DEVICE';
    /**
     * API Değeri: EXTERNAL_BASKET_WITH_APP
     */
    case EXTERNALBASKETWITHAPP = 'EXTERNAL_BASKET_WITH_APP';

    /**
     * Tüm değerleri array olarak döner.
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}