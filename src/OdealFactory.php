<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * Framework entegrasyon helper'ı.
 * 
 * Ortam değişkenlerinden veya Laravel config'den OdealClient oluşturmayı kolaylaştırır.
 * 
 * Ortam Değişkenleri Kullanımı:
 *   $client = OdealFactory::fromEnvironment();
 * 
 * Laravel Service Provider'da:
 *   $this->app->singleton(OdealClient::class, fn() => OdealFactory::fromEnvironment());
 * 
 * Symfony Service Tanımı (services.yaml):
 *   Odeal\Sdk\OdealClient:
 *     factory: ['Odeal\Sdk\OdealFactory', 'fromEnvironment']
 */
class OdealFactory
{
    /**
     * Ortam değişkenlerinden OdealClient oluşturur.
     *
     * Aşağıdaki env değişkenlerini okur:
     *   ODEAL_SECRET_KEY (zorunlu)
     *   ODEAL_MERCHANT_KEY (zorunlu)
     *   ODEAL_BASE_URL (opsiyonel)
     *   ODEAL_ENVIRONMENT (opsiyonel — production, prod, staging, stage, sandbox; başka değer hata)
     *   ODEAL_SANDBOX (opsiyonel — true/false/1/0; başka değer hata)
     *   ODEAL_DEBUG (opsiyonel, "true"/"false")
     *   ODEAL_TIMEOUT (opsiyonel, saniye)
     *   ODEAL_MAX_RETRY (opsiyonel)
     *
     * Adres önceliği OdealConfig::getEffectiveBaseUrl()'dedir (davranış sözleşmesi §12):
     *   ODEAL_SANDBOX açık > ODEAL_BASE_URL > ODEAL_ENVIRONMENT > production.
     *
     * @param array<string, mixed> $overrides Config üzerine yazılacak değerler
     * @return OdealClient
     * @throws \RuntimeException Zorunlu env eksikse
     * @throws \InvalidArgumentException ODEAL_ENVIRONMENT ya da ODEAL_SANDBOX geçersizse
     */
    public static function fromEnvironment(array $overrides = []): OdealClient
    {
        return new OdealClient(self::configFromEnvironment($overrides));
    }

    /**
     * Ortam değişkenlerinden OdealConfig oluşturur; kurallar fromEnvironment() ile aynıdır.
     * Kendi Guzzle istemcinizle: new OdealClient(OdealFactory::configFromEnvironment(), $guzzle).
     *
     * @param array<string, mixed> $overrides Config üzerine yazılacak değerler
     * @throws \RuntimeException Zorunlu env eksikse
     * @throws \InvalidArgumentException ODEAL_ENVIRONMENT ya da ODEAL_SANDBOX geçersizse
     */
    public static function configFromEnvironment(array $overrides = []): OdealConfig
    {
        $secretKey = getenv('ODEAL_SECRET_KEY') ?: ($overrides['secretKey'] ?? null);
        $merchantKey = getenv('ODEAL_MERCHANT_KEY') ?: ($overrides['merchantKey'] ?? null);

        if (!$secretKey) {
            throw new \RuntimeException('ODEAL_SECRET_KEY environment variable must be set.');
        }
        if (!$merchantKey) {
            throw new \RuntimeException('ODEAL_MERCHANT_KEY environment variable must be set.');
        }

        // Ortam ve adres; öncelik OdealConfig::getEffectiveBaseUrl()'de. Bilinmeyen değer production'a düşmez.
        $baseUrl = trim((string) getenv('ODEAL_BASE_URL'));
        $environmentName = trim((string) getenv('ODEAL_ENVIRONMENT'));
        try {
            $environment = $environmentName !== '' ? OdealEnvironment::fromString($environmentName) : OdealEnvironment::PRODUCTION;
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('ODEAL_ENVIRONMENT geçersiz: ' . $e->getMessage(), 0, $e);
        }
        $sandboxMode = self::parseFlag('ODEAL_SANDBOX', getenv('ODEAL_SANDBOX'));
        $debugMode = strtolower(getenv('ODEAL_DEBUG') ?: '') === 'true';
        $timeout = getenv('ODEAL_TIMEOUT') ? (int) getenv('ODEAL_TIMEOUT') : 30;
        $maxRetry = getenv('ODEAL_MAX_RETRY') ? (int) getenv('ODEAL_MAX_RETRY') : 3;

        $config = new OdealConfig(
            secretKey: $overrides['secretKey'] ?? $secretKey,
            merchantKey: $overrides['merchantKey'] ?? $merchantKey,
            baseUrl: $overrides['baseUrl'] ?? $baseUrl,
            debugMode: $overrides['debugMode'] ?? $debugMode,
            timeout: $overrides['timeout'] ?? $timeout,
            maxRetryCount: $overrides['maxRetryCount'] ?? $maxRetry,
            sandboxMode: $overrides['sandboxMode'] ?? $sandboxMode,
            environment: $overrides['environment'] ?? $environment,
        );

        return $config;
    }

    /** "true"/"1" açık; "false"/"0"/boş kapalı; başka değer hata (sessizce production'a düşülmez). */
    private static function parseFlag(string $name, string|false $value): bool
    {
        $text = strtr(trim((string) $value), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
        if ($text === '' || $text === 'false' || $text === '0') {
            return false;
        }
        if ($text === 'true' || $text === '1') {
            return true;
        }
        throw new \InvalidArgumentException("{$name} geçersiz: '" . (string) $value . "'. Geçerli değerler: true, false, 1, 0.");
    }
}
