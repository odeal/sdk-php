<?php

namespace Odeal\Sdk;

/**
 * Odeal API ortam tanımları.
 *
 * Taban adres önceliği (davranış sözleşmesi §12): çağrı başına adres, sandbox modu, açık baseUrl, ortam,
 * varsayılan (production).
 *
 * Kullanım:
 * ```php
 * $config = OdealConfigBuilder::create()
 *     ->secretKey('sk_xxx')
 *     ->merchantKey('mk_xxx')
 *     ->environment(OdealEnvironment::STAGING)
 *     ->build();
 * ```
 */
class OdealEnvironment
{
    /** Test ortamı (sandbox) */
    public const STAGING = 'staging';

    /** Canlı (production) ortamı */
    public const PRODUCTION = 'production';

    /** @var array<string, string> Ortam → URL haritası */
    private static array $urls = [
        self::STAGING    => 'https://api.sandbox.odeal.com/integration/api/v1',
        self::PRODUCTION => 'https://api.odeal.com/api/v1',
    ];

    /**
     * Ortamın API taban adresini döner. Ad, fromString() kurallarıyla çözülür.
     *
     * @throws \InvalidArgumentException Ortam adı tanınmıyorsa; bilinmeyen ad production'a düşmez
     */
    public static function getBaseUrl(string $environment): string
    {
        return self::$urls[self::fromString($environment)];
    }

    /**
     * Ortam adını çözer: "production" ve "prod" → PRODUCTION; "staging", "stage" ve "sandbox" → STAGING.
     * Büyük/küçük harf ve baştaki/sondaki boşluk önemsizdir.
     *
     * @throws \InvalidArgumentException Ad boşsa ya da tanınmıyorsa; bilinmeyen ad production'a düşmez
     */
    public static function fromString(?string $value): string
    {
        // strtolower PHP 8.2 öncesinde yerele bağlı (Türkçe yerelde "I" farklı küçülebilir); yalnız ASCII çevrilir.
        $name = strtr(trim((string) $value), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
        if (in_array($name, ['production', 'prod'], true)) {
            return self::PRODUCTION;
        }
        if (in_array($name, ['staging', 'stage', 'sandbox'], true)) {
            return self::STAGING;
        }
        throw new \InvalidArgumentException(
            "Bilinmeyen Odeal ortamı: '" . (string) $value . "'. Geçerli değerler: production, prod, staging, stage, sandbox."
        );
    }
}
