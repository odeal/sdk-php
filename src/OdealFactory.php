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
     *   ODEAL_DEBUG (opsiyonel, "true"/"false")
     *   ODEAL_TIMEOUT (opsiyonel, saniye)
     *   ODEAL_MAX_RETRY (opsiyonel)
     *
     * @param array<string, mixed> $overrides Config üzerine yazılacak değerler
     * @return OdealClient
     * @throws \RuntimeException Zorunlu env eksikse
     */
    public static function fromEnvironment(array $overrides = []): OdealClient
    {
        $secretKey = getenv('ODEAL_SECRET_KEY') ?: ($overrides['secretKey'] ?? null);
        $merchantKey = getenv('ODEAL_MERCHANT_KEY') ?: ($overrides['merchantKey'] ?? null);

        if (!$secretKey) {
            throw new \RuntimeException('ODEAL_SECRET_KEY environment variable must be set.');
        }
        if (!$merchantKey) {
            throw new \RuntimeException('ODEAL_MERCHANT_KEY environment variable must be set.');
        }

        $baseUrl = getenv('ODEAL_BASE_URL') ?: 'https://api.odeal.com/v1';
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
        );

        return new OdealClient($config);
    }
}
