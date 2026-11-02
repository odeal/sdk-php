<?php

declare(strict_types=1);

namespace Odeal\Sdk;



/**
 * Odeal SDK yapılandırma ayarları.
 *
 * Logger için PSR-3 uyumlu herhangi bir logger (Monolog vb.) ya da OdealLoggerInterface kullanılabilir.
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
        /** Açık taban adres; boşsa ortamın adresi kullanılır (sözleşme §12). */
        public string $baseUrl = '',
        public ?string $externalDeviceKey = null,
        public bool $skipClientValidation = false,
        public bool $debugMode = false,

        // --- ENTERPRISE YETENEKLERİ ---
        public int $timeout = 30,
        public int $maxRetryCount = 3,
        public bool $maskSensitiveData = true,
        /** PSR-3 logger (Monolog vb.) ya da OdealLoggerInterface; mesajlar debug/info/warning/error'a gider. */
        public OdealLoggerInterface|\Psr\Log\LoggerInterface|null $logger = null,
        /** @var OdealInterceptorInterface[] */
        public array $interceptors = [],

        // --- CIRCUIT BREAKER ---
        public bool $circuitBreakerEnabled = false,
        public int $circuitBreakerThreshold = 5,
        public int $circuitBreakerResetMs = 60000,

        /** Sandbox (test) modu. Açıkken istekler baseUrl ve ortamdan bağımsız olarak test ortamına gider (§12). */
        public bool $sandboxMode = false,

        /** OAuth2 / Bearer erişim token'ı. Ayarlandığında her isteğe Authorization: Bearer eklenir. */
        public ?string $accessToken = null,

        /** Ortam (OdealEnvironment::PRODUCTION / STAGING ya da takma adı). Açık baseUrl yoksa adresi belirler. */
        public string $environment = OdealEnvironment::PRODUCTION,

        /**
         * POST/PUT/PATCH isteklerine X-Odeal-Idempotency-Key başlığı eklenir mi (varsayılan: kapalı). Ödeal API'si
         * bu başlığı henüz kullanmıyor; sepetin benzersiz anahtarı basketReferenceCode'dur (sözleşme §3).
         */
        public bool $enableIdempotencyKey = false
    ) {
        // Ad hemen çözülür; bilinmeyen ad burada hata verir (sözleşme §12).
        $this->environment = OdealEnvironment::fromString($this->environment);
    }

    /**
     * İsteklerin gönderildiği taban adres (sözleşme §12): sandboxMode açıksa test ortamı; değilse açık baseUrl;
     * o da yoksa ortamın adresi (varsayılan production). Çağrı başına verilen adres bundan da önceliklidir (§11).
     *
     * @throws \InvalidArgumentException environment sonradan tanınmayan bir değere çekilmişse
     */
    public function getEffectiveBaseUrl(): string
    {
        if ($this->sandboxMode) {
            return OdealEnvironment::getBaseUrl(OdealEnvironment::STAGING);
        }
        if ($this->baseUrl !== '') {
            return $this->baseUrl;
        }
        return OdealEnvironment::getBaseUrl($this->environment);
    }

    /**
     * Config'i yazdırırken hassas alanları maskeler.
     */
    public function __toString(): string
    {
        return sprintf(
            'OdealConfig{baseUrl=%s, environment=%s, sandboxMode=%s, secretKey=%s, merchantKey=%s, debugMode=%s, timeout=%d, maxRetry=%d}',
            $this->baseUrl,
            $this->environment,
            $this->sandboxMode ? 'true' : 'false',
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
