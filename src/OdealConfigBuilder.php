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
 *       ->baseUrl('https://api.odeal.com/v1')
 *       ->debugMode(true)
 *       ->timeout(60)
 *       ->maxRetryCount(5)
 *       ->build();
 */
class OdealConfigBuilder
{
    private string $secretKey = '';
    private string $merchantKey = '';
    private string $baseUrl = 'https://api.odeal.com/v1';
    private ?string $externalDeviceKey = null;
    private bool $skipClientValidation = false;
    private bool $debugMode = false;
    private int $timeout = 30;
    private int $maxRetryCount = 3;
    private bool $maskSensitiveData = true;
    private array $interceptors = [];

    public static function create(): self { return new self(); }

    public function secretKey(string $key): self { $this->secretKey = $key; return $this; }
    public function merchantKey(string $key): self { $this->merchantKey = $key; return $this; }
    public function baseUrl(string $url): self { $this->baseUrl = $url; return $this; }
    /** Ortam preset'i. BaseUrl'i otomatik ayarlar. */
    public function environment(string $env): self { $this->baseUrl = OdealEnvironment::getBaseUrl($env); return $this; }
    /** Sandbox (test) modu. Aktifken her zaman staging ortamı kullanılır. */
    public function sandboxMode(bool $sandbox = true): self { if ($sandbox) $this->baseUrl = OdealEnvironment::getBaseUrl(OdealEnvironment::STAGING); return $this; }
    public function externalDeviceKey(string $key): self { $this->externalDeviceKey = $key; return $this; }
    public function skipClientValidation(bool $skip = true): self { $this->skipClientValidation = $skip; return $this; }
    public function debugMode(bool $debug = true): self { $this->debugMode = $debug; return $this; }
    public function timeout(int $timeout): self { $this->timeout = $timeout; return $this; }
    public function maxRetryCount(int $count): self { $this->maxRetryCount = $count; return $this; }
    public function maskSensitiveData(bool $mask = true): self { $this->maskSensitiveData = $mask; return $this; }
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
        );
    }
}
