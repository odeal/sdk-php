<?php

namespace Odeal\Sdk;

/**
 * Odeal API ortam tanımları.
 *
 * Kullanım:
 * ```php
 * $config = OdealConfigBuilder::create()
 *     ->secretKey('sk_xxx')
 *     ->environment(OdealEnvironment::PRODUCTION)
 *     ->build();
 * ```
 */
class OdealEnvironment
{
    /** Test/geliştirme ortamı */
    public const STAGING = 'staging';

    /** Canlı (production) ortamı */
    public const PRODUCTION = 'production';

    /** @var array<string, string> Ortam → URL haritası */
    private static array $urls = [
        self::STAGING    => 'https://stage.odealapp.com/api/v1',
        self::PRODUCTION => 'https://api.odeal.com/v1',
    ];

    /**
     * Ortam için API base URL'ini döner.
     */
    public static function getBaseUrl(string $environment): string
    {
        return self::$urls[$environment] ?? self::$urls[self::PRODUCTION];
    }

    /**
     * String'den ortam çözümler.
     */
    public static function fromString(?string $value): string
    {
        if ($value === null) return self::STAGING;
        $lower = strtolower($value);
        return in_array($lower, ['production', 'prod']) ? self::PRODUCTION : self::STAGING;
    }
}
