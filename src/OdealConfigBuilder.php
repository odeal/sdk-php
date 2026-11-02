<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * OdealConfig fluent builder.
 *
 * Kullanım:
 *   $config = OdealConfigBuilder::create()
 *       ->secretKey('sk_xxx')
 *       ->merchantKey('mk_xxx')
 *       ->environment(OdealEnvironment::STAGING)
 *       ->debugMode(true)
 *       ->timeout(60)
 *       ->maxRetryCount(5)
 *       ->build();
 */
class OdealConfigBuilder
{
    private string $secretKey = '';
    private string $merchantKey = '';
    private string $baseUrl = '';
    private string $environment = OdealEnvironment::PRODUCTION;
    private bool $sandboxMode = false;
    private ?string $externalDeviceKey = null;
    private bool $skipClientValidation = false;
    private bool $debugMode = false;
    private int $timeout = 30;
    private int $maxRetryCount = 3;
    private bool $maskSensitiveData = true;
    private bool $enableIdempotencyKey = false;
    private array $interceptors = [];

    public static function create(): self { return new self(); }

    public function secretKey(string $key): self { $this->secretKey = $key; return $this; }
    public function merchantKey(string $key): self { $this->merchantKey = $key; return $this; }
    public function baseUrl(string $url): self { $this->baseUrl = $url; return $this; }
    /** Ortam (ya da takma adı). Açık baseUrl verilmişse adres o olur; çağrı sırası önemsizdir (§12). */
    public function environment(string $env): self { $this->environment = OdealEnvironment::fromString($env); return $this; }
    /** Sandbox (test) modu. Açıkken istekler her zaman test ortamına gider (§12). */
    public function sandboxMode(bool $sandbox = true): self { $this->sandboxMode = $sandbox; return $this; }
    public function externalDeviceKey(string $key): self { $this->externalDeviceKey = $key; return $this; }
    public function skipClientValidation(bool $skip = true): self { $this->skipClientValidation = $skip; return $this; }
    public function debugMode(bool $debug = true): self { $this->debugMode = $debug; return $this; }
    public function timeout(int $timeout): self { $this->timeout = $timeout; return $this; }
    public function maxRetryCount(int $count): self { $this->maxRetryCount = $count; return $this; }
    public function maskSensitiveData(bool $mask = true): self { $this->maskSensitiveData = $mask; return $this; }
    /** POST/PUT/PATCH isteklerine idempotency başlığı ekler (varsayılan kapalı; sözleşme §3). */
    public function enableIdempotencyKey(bool $enable = true): self { $this->enableIdempotencyKey = $enable; return $this; }
    public function addInterceptor(OdealInterceptorInterface $interceptor): self { $this->interceptors[] = $interceptor; return $this; }

    /**
     * OdealConfig'i oluşturur ve zorunlu alanları doğrular.
     *
     * @return OdealConfig
     * @throws \InvalidArgumentException Zorunlu alanlar eksikse
     */
    public function build(): OdealConfig
    {
        if (empty($this->secretKey)) {
            throw new \InvalidArgumentException('secretKey is required.');
        }
        if (empty($this->merchantKey)) {
            throw new \InvalidArgumentException('merchantKey is required.');
        }

        return new OdealConfig(
            secretKey: $this->secretKey,
            merchantKey: $this->merchantKey,
            baseUrl: $this->baseUrl,
            externalDeviceKey: $this->externalDeviceKey,
            skipClientValidation: $this->skipClientValidation,
            debugMode: $this->debugMode,
            timeout: $this->timeout,
            maxRetryCount: $this->maxRetryCount,
            maskSensitiveData: $this->maskSensitiveData,
            interceptors: $this->interceptors,
            sandboxMode: $this->sandboxMode,
            environment: $this->environment,
            enableIdempotencyKey: $this->enableIdempotencyKey,
        );
    }
}
