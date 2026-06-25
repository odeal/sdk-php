# Odeal SDK for PHP

> Odeal Entegrasyon SDK (Otomatik Üretildi)

> **Version:** 0.1.3 | **License:** MIT | **Auto-Generated** by Odeal SDK Generator


## Installation

```bash
composer require odeal/sdk
```

## Requirements

- PHP >= 8.1
- Guzzle HTTP client

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use Odeal\Sdk\OdealClient;
use Odeal\Sdk\OdealConfig;

$config = new OdealConfig(
    secretKey: 'your-secret-key',
    merchantKey: 'your-merchant-key',
);
$client = new OdealClient($config);

// SDK iki kullanım biçimini de destekler — ikisi de eşdeğerdir, aynı metoda gider:
$response = $client->createSimpleBasket($request);           // 1) Flat (doğrudan)
$response = $client->basket()->createSimpleBasket($request); // 2) Grouped (resource üzerinden)
```

## Configuration

### Builder Pattern

```php
use Odeal\Sdk\OdealConfigBuilder;

$config = OdealConfigBuilder::create()
    ->secretKey('sk_xxx')
    ->merchantKey('mk_xxx')
    ->baseUrl('https://api.odeal.com/v1')
    ->debugMode(true)
    ->timeout(60)
    ->maxRetryCount(5)
    ->build();

$client = new OdealClient($config);
```

### Factory / Environment Variables

```php
use Odeal\Sdk\OdealFactory;

// Reads: ODEAL_SECRET_KEY, ODEAL_MERCHANT_KEY
$client = OdealFactory::fromEnvironment();
```

### Laravel

```php
// AppServiceProvider.php
$this->app->singleton(OdealClient::class, fn() => OdealFactory::fromEnvironment());

// In controllers:
public function __construct(private OdealClient $odeal) {}
```

### Symfony

```yaml
# services.yaml
Odeal\Sdk\OdealClient:
    factory: ['Odeal\Sdk\OdealFactory', 'fromEnvironment']
```

## Interceptors

```php
use Odeal\Sdk\OdealInterceptorInterface;
use Odeal\Sdk\RequestContext;
use Odeal\Sdk\ResponseContext;

class LoggingInterceptor implements OdealInterceptorInterface
{
    public function onBeforeRequest(RequestContext $ctx): void {
        echo "→ {$ctx->method} {$ctx->url}\n";
    }

    public function onAfterResponse(ResponseContext $ctx): void {
        echo "← {$ctx->statusCode} ({$ctx->durationMs}ms)\n";
    }
}

$config = OdealConfigBuilder::create()
    ->secretKey('sk_xxx')
    ->merchantKey('mk_xxx')
    ->addInterceptor(new LoggingInterceptor())
    ->build();
```

## Error Handling

```php
use Odeal\Sdk\OdealApiException;
use Odeal\Sdk\OdealAuthenticationException;
use Odeal\Sdk\OdealRateLimitException;
use Odeal\Sdk\OdealNotFoundException;

try {
    $client->basket()->createSimpleBasket($request);
} catch (OdealRateLimitException $e) {
    echo "Rate limit! Retry: {$e->getRetryAfterSeconds()}s\n";
} catch (OdealAuthenticationException $e) {
    echo "Invalid API keys!\n";
} catch (OdealNotFoundException $e) {
    echo "Resource not found\n";
} catch (OdealApiException $e) {
    echo "API Error: {$e->getStatusCode()} - {$e->getMessage()}\n";
}
```

## Circuit Breaker

```php
$config = new OdealConfig(
    secretKey: 'sk_xxx',
    merchantKey: 'mk_xxx',
    circuitBreakerEnabled: true,
    circuitBreakerThreshold: 5,    // 5 consecutive failures → circuit opens
    circuitBreakerResetMs: 60000,  // 60s cooldown before half-open test
);

try {
    $client->basket()->createSimpleBasket($request);
} catch (OdealCircuitOpenException $e) {
    echo "Circuit open — requests temporarily blocked.\n";
}
```

## Request Logger

Built-in `OdealRequestLogger` middleware for production HTTP traffic logging:

```php
use Odeal\Sdk\OdealRequestLogger;

$logger = new OdealRequestLogger(
    level: 'info',
    maskFields: ['password', 'cvv', 'cardNumber', 'tckn', 'iban', 'email', 'phone', 'address'],
    logBody: true,
    logResponseBody: false,
    minDurationMs: 0,
);

$client = new OdealClient(new OdealConfig(
    secretKey: 'sk_xxx',
    interceptors: [$logger]
));

// Output:
// [ODEAL INFO] → POST https://api.odeal.com/basket/simple
//   Body: {"merchantKey":"mk_xxx","password":"***"}
// [ODEAL INFO] ← 200 POST https://api.odeal.com/basket/simple (142ms)
```

PSR-3 compatible logger:

```php
$logger = new OdealRequestLogger(
    logger: fn($msg, $lvl) => $psrLogger->log($lvl, $msg)
);
```

## Timeout & Retry

```php
$config = new OdealConfig(
    secretKey: 'sk_xxx',
    merchantKey: 'mk_xxx',
    timeout: 60,           // Default: 30s
    maxRetryCount: 5,      // Default: 3
);

// Automatic retry on 5xx and 429 with exponential backoff
// Retry-After header is respected
```

## Webhook Verification

Gelen Odeal webhook'larının gerçekten Odeal'den geldiğini HMAC-SHA256 imzasıyla doğrulayın.
İmza `X-Odeal-Signature` header'ında, **ham gövde** üzerinden hesaplanarak gelir.

```php
use Odeal\Sdk\OdealWebhookVerifier;

// Laravel controller veya düz PHP
$payload = file_get_contents('php://input'); // ham gövde
$signature = $_SERVER['HTTP_X_ODEAL_SIGNATURE'] ?? '';

if (!OdealWebhookVerifier::verifySignature($payload, $signature, 'your-webhook-secret')) {
    http_response_code(401);
    exit('Invalid webhook signature');
}

// İmza geçerli — webhook olayını işle
http_response_code(200);
```

Replay koruması için timestamp doğrulamalı sürüm (önerilir):

```php
$valid = OdealWebhookVerifier::verifySignatureWithTimestamp(
    $payload, $signature, $timestamp, 'your-webhook-secret'); // varsayılan 5 dk tolerans
```

## Features

- ✅ PHP 8.1+ compatible
- ✅ Builder pattern for clean configuration
- ✅ Laravel & Symfony integration
- ✅ Interceptor pipeline (request/response hooks)
- ✅ Rich error hierarchy (Auth, Forbidden, NotFound, RateLimit, Validation)
- ✅ Automatic retry with exponential backoff & Retry-After header
- ✅ Configurable timeout
- ✅ Idempotency key injection for POST/PUT/PATCH
- ✅ Client-side input validation
- ✅ Sensitive data masking in debug logs
- ✅ Response unwrapping (`{"result": [...]}` → auto-extract)
- ✅ Circuit breaker pattern
- ✅ Request Logger middleware
- ✅ PSR-3 logging support
- ✅ Guzzle HTTP client
- ✅ Webhook signature verification
- ✅ Health check utility

## API Reference
### BasketResource

| Method | Description |
|:-------|:------------|
| `createSimpleBasket()` | Standart ürün satışı. Müşteri Bireysel veya Kurumsal olabilir. 'items' alanı zorunludur. |
| `createAdvanceBasket()` | Avans tahsilatı. Müşteri Bireysel veya Kurumsal olabilir. 'items' gönderilmez. `basketType` ADVANCE olmalıdır. |
| `createCurrentAccountBasket()` | Cari hesap tahsilatı. Müşteri Kurumsal olmalıdır. `basketType` CURRENT_ACCOUNT olmalıdır. |
| `createFoodCardBasket()` | Yemek kartı işlemleri. `receiptInfo` ve içindeki `foodCardBrandId` zorunludur. |
| `listBaskets()` | Sepet Listele |
| `deleteBasket()` | Sepet Sil |
| `deleteAllBaskets()` | Tüm Sepetleri Sil |
### PaymentResource

| Method | Description |
|:-------|:------------|
| `cancelPayment()` | Ödeme İptali |
### ConfigurationResource

| Method | Description |
|:-------|:------------|
| `saveConfiguration()` | Konfigürasyon Kaydet |
| `getConfiguration()` | Konfigürasyon Getir |
### UnitResource

| Method | Description |
|:-------|:------------|
| `listUnits()` | Birimleri Listele |
### ReportResource

| Method | Description |
|:-------|:------------|
| `getTransactionReport()` | İşlem Raporu |

## 🔐 Güvenlik & Doğrulama

Bu paket **Sigstore (cosign)** ile imzalanır ve doğrulanabilir kanıtlarla (attestation) yayınlanır:

- **İmza** — keyless cosign imzası; kimlik, yayınlayan CI workflow'una OIDC ile bağlanır
- **SBOM** — CycloneDX yazılım malzeme listesi (imzalı attestation)
- **Zafiyet taraması** — grype taraması (imzalı attestation)
- **Provenance** — SLSA build-provenance attestation'ı

### Doğrulama

```bash
# Paket imzası
cosign verify-blob \
  --bundle sign.bundle.json \
  --certificate-oidc-issuer https://token.actions.githubusercontent.com \
  --certificate-identity-regexp "<yayın-workflow-url>" \
  <paket-dosyası>

# SBOM attestation
cosign verify-blob-attestation \
  --bundle sbom.bundle.json --type cyclonedx \
  --certificate-oidc-issuer https://token.actions.githubusercontent.com \
  --certificate-identity-regexp "<yayın-workflow-url>" \
  <paket-dosyası>
```

Güvenlik açığı bildirimi ve tam talimatlar için: [`SECURITY.md`](./SECURITY.md)


## License

MIT
