<?php

declare(strict_types=1);

namespace Odeal\Sdk;



/**
 * Odeal SDK yapılandırma ayarları.
 * 
 * Logger için PSR-3 uyumlu herhangi bir logger (Monolog vb.) kullanılabilir.
 * Belirtilmezse error_log() kullanılır.
 * 
 * @example
 * ```php
 * $config = new OdealConfig(
 *     secretKey: 'sk_xxx',
 *     merchantKey: 'mk_xxx',
 *     logger: new \Monolog\Logger('odeal')
 * );
 * ```
 */
class OdealConfig
{
    public function __construct(
        public string $secretKey,
        public string $merchantKey,
        public string $baseUrl = 'https://api.odeal.com/v1',
        public ?string $externalDeviceKey = null,
        public bool $skipClientValidation = false,
        public bool $debugMode = false,
        
        // --- ENTERPRISE YETENEKLERİ ---
        public int $timeout = 30,
        public int $maxRetryCount = 3,
        public bool $maskSensitiveData = true,
        public ?OdealLoggerInterface $logger = null,
        /** @var OdealInterceptorInterface[] */
        public array $interceptors = [],
        
        // --- CIRCUIT BREAKER ---
        public bool $circuitBreakerEnabled = false,
        public int $circuitBreakerThreshold = 5,
        public int $circuitBreakerResetMs = 60000,
        
        /** Sandbox (test) modu. True olduğunda baseUrl değerine bakılmaksızın staging ortamı kullanılır. */
        public bool $sandboxMode = false,

        /** OAuth2 / Bearer erişim token'ı. Ayarlandığında her isteğe Authorization: Bearer eklenir. */
        public ?string $accessToken = null
    ) {}

    /**
     * Efektif base URL — sandboxMode aktifse staging, değilse baseUrl döner.
     */
    public function getEffectiveBaseUrl(): string
    {
        return $this->sandboxMode
            ? OdealEnvironment::getBaseUrl(OdealEnvironment::STAGING)
            : $this->baseUrl;
    }

    /**
     * Config'i yazdırırken hassas alanları maskeler.
     */
    public function __toString(): string
    {
        return sprintf(
            'OdealConfig{baseUrl=%s, secretKey=%s, merchantKey=%s, debugMode=%s, timeout=%d, maxRetry=%d}',
            $this->baseUrl,
            self::maskSecret($this->secretKey),
            self::maskSecret($this->merchantKey),
            $this->debugMode ? 'true' : 'false',
            $this->timeout,
            $this->maxRetryCount
        );
    }

    private static function maskSecret(string $secret): string
    {
        if (empty($secret)) return '(empty)';
        if (strlen($secret) <= 8) return '***';
        return substr($secret, 0, 4) . '***' . substr($secret, -4);
    }
}