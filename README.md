# Odeal SDK for PHP

> Odeal Entegrasyon SDK (Otomatik Üretildi)

> **Version:** 0.2.0 | **License:** MIT | **Auto-Generated** by Odeal SDK Generator


## Installation

`odeal/sdk` is not on Packagist yet. Add the GitHub repository
([github.com/odeal/sdk-php](https://github.com/odeal/sdk-php)) and require the package:

```bash
composer config repositories.odeal vcs https://github.com/odeal/sdk-php
composer require odeal/sdk:dev-main
```

## Versioning

The five Ödeal SDKs (C#, TypeScript, Python, PHP, Java) share one version number and follow
[Semantic Versioning](https://semver.org/). While the version is 0.x, a minor release (0.2 → 0.3) may contain breaking
changes and a patch release (0.2.x) does not. Each release is a Git tag; a published tag never changes. Release notes:
[Releases](https://github.com/odeal/sdk-php/releases).

To use exactly this version (0.2.0), require its tag instead of `dev-main`:

```bash
composer require odeal/sdk:0.2.0
```

To upgrade, require the new version the same way.

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
use Odeal\Sdk\OdealEnvironment;

$config = OdealConfigBuilder::create()
    ->secretKey('sk_xxx')
    ->merchantKey('mk_xxx')
    ->environment(OdealEnvironment::STAGING) // test; default is PRODUCTION
    ->debugMode(true)
    ->timeout(60)
    ->maxRetryCount(5)
    ->build();

$client = new OdealClient($config);
```

### Environments

| `OdealEnvironment` | Base URL |
|---|---|
| `PRODUCTION` (default) | `https://api.odeal.com/api/v1` |
| `STAGING` (test / sandbox) | `https://api.sandbox.odeal.com/integration/api/v1` |

Request address, first match wins:

1. A per-call base URL, for that request only.
2. `sandboxMode: true`: always the test environment.
3. An explicit `baseUrl`.
4. `environment`.
5. Production.

`OdealFactory::fromEnvironment()` also reads `ODEAL_ENVIRONMENT` (`production`, `prod`, `staging`, `stage`, `sandbox`),
`ODEAL_SANDBOX` (`true`, `false`, `1`, `0`) and `ODEAL_BASE_URL`. An unknown value throws instead of
falling back to production.

### Factory / Environment Variables

```php
use Odeal\Sdk\OdealFactory;

// Reads: ODEAL_SECRET_KEY, ODEAL_MERCHANT_KEY (+ ODEAL_ENVIRONMENT, ODEAL_SANDBOX, ODEAL_BASE_URL)
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
use Odeal\Sdk\OdealEnvironment;
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
    interceptors: [$logger],
    environment: OdealEnvironment::STAGING, // test; default is PRODUCTION
));

// Output:
// [ODEAL INFO] → POST https://api.sandbox.odeal.com/integration/api/v1/basket
//   Body: {"merchantKey":"mk_xxx","password":"***"}
// [ODEAL INFO] ← 200 POST https://api.sandbox.odeal.com/integration/api/v1/basket (142ms)
```

PSR-3 compatible logger:

```php
$logger = new OdealRequestLogger(
    logger: fn($msg, $lvl) => $psrLogger->log($lvl, $msg)
);
```

`OdealConfig` also takes a PSR-3 logger (for example Monolog) as `logger:`; SDK messages go to its `debug`,
`info`, `warning` and `error` methods.

## Timeout & Retry

```php
$config = new OdealConfig(
    secretKey: 'sk_xxx',
    merchantKey: 'mk_xxx',
    timeout: 60,           // Default: 30s
    maxRetryCount: 5,      // Default: 3
);

// GET requests only: automatic retry on 5xx, 429 and network errors with exponential backoff.
// POST, PUT, PATCH and DELETE are never retried (the server may already have processed them).
// Async methods (...Async) are never retried, GET included.
// Retry-After header is respected
```

## Redirects

The SDK never follows redirects. A 3xx response throws `OdealRedirectException` with the original status code and is not retried. The SDK sets `allow_redirects => false` on every request, so this also applies to a Guzzle client you pass in. If you see it, check `baseUrl`.

## Account Settings (Configuration and Profile)

**Configuration.** `saveConfiguration` replaces the whole merchant configuration on the Ödeal side: every field you do not
send is cleared. The request model has the documented fields only, so settings outside them (for example
`basketCancelledUrl`, or a callback authentication set up by the Ödeal integration team) are cleared as well.
Before saving, read the current values with `getConfiguration` and send all documented fields again. If your account
uses settings outside the documented fields, ask the Ödeal integration team before saving.

**Profile.** `updateProfile` changes your integration profile type, which decides which basket flow the API accepts.
Sending baskets with this SDK needs `EXTERNAL_DEVICE_TO_DEVICE`. Change the type only on purpose; for
production, use production keys. `getProfileDetails` returns your own `apiKey` and `secretKey`: do not log or store
the response. `profileType` is returned as a string, because the API can return types that are not in the
documentation.

## Discounted Baskets

Basket lines can carry discounts (`allowances`) on `POST /basket`. Amounts include VAT, and a line discount applies to
the whole line (quantity × unit price), not to one unit. A discounted basket also needs `price->totalAllowance` (the sum
of the line discounts, exact to the kuruş) and `price->paidAmount` (`grossPrice − totalAllowance`). The payment options
must add up to `paidAmount`.

`OdealAllowances` does this arithmetic for amount-based discounts:

```php
use Odeal\Sdk\OdealAllowances;
use Odeal\Sdk\Enums\PaymentOptionType;
use Odeal\Sdk\Models\BasketPrice;
use Odeal\Sdk\Models\BasketRequest;
use Odeal\Sdk\Models\PaymentOption;

$items = [/* Item: Gözleme 2 × 120.00, Ayran 3 × 35.00, Kola 1 × 60.00 */];
$price = new BasketPrice();
$price->grossPrice = 405.00;

// 40.00 off the subtotal, split by line amount, 2 decimals, remainder on the last line.
// Each line gets a SUB discount (23.70, 10.37, 5.93); totalAllowance = 40.00, paidAmount = 365.00.
OdealAllowances::applySubtotalAllowance($price, $items, 40.00);

$payment = new PaymentOption();
$payment->type = PaymentOptionType::CASH;
$payment->amount = $price->paidAmount;

$request = new BasketRequest();
$request->referenceCode = 'ISK-0001';
$request->customer = $customer;
$request->price = $price;
$request->items = $items;
$request->paymentOptions = [$payment];
$response = $client->createSimpleBasket($request);
```

- For line discounts you add yourself (`subType = AllowanceSubType::LINE`), call
  `OdealAllowances::applyAllowanceTotals($price, $items)` to fill `totalAllowance` and `paidAmount`.
- If one line has a `SUB` discount, every line needs one (API error 2080). `applySubtotalAllowance` adds one to each
  line.
- Rate-based discounts (`isRateBased = true`) are sent as they are, but the helpers do not total them: Ödeal calculates
  their amount.
- After discounts the basket total must be at least 1 TL (API error 2304). The helpers check this and
  their other rules before they change anything, and throw `OdealValidationException` when a rule fails.
  `applySubtotalAllowance` needs a separate `Item` object for each line.
- Ödeal accepts a difference of 1 kuruş between `paidAmount` and its own calculation. The receipt shows the discount
  with VAT and the e-invoice without VAT, so the two numbers can differ.

## Webhook Verification

Ödeal posts payment and basket notifications as JSON to the URLs in your configuration. If you set an
`odealRequestKey`, every notification carries it in the `X-ODEAL-REQUEST-KEY` header; Basic Auth can be set up with
the Ödeal integration team instead. Verification is optional: use the helper that matches your setup. Both helpers
compare in constant time and return `false` for a missing header or an empty expected value.

```php
use Odeal\Sdk\OdealWebhookVerifier;

$requestKey = $_SERVER['HTTP_X_ODEAL_REQUEST_KEY'] ?? null;
if (!OdealWebhookVerifier::verifyRequestKey($requestKey, 'your-odeal-request-key')) {
    http_response_code(401);
    exit;
}

http_response_code(200);

// Basic Auth instead of the key:
$valid = OdealWebhookVerifier::verifyBasicAuth($_SERVER['HTTP_AUTHORIZATION'] ?? null, 'user', 'password');
```

Respond with a 2xx status quickly and do heavy work afterwards. Redelivery of failed notifications is not
guaranteed, and the same notification can arrive more than once: deduplicate by `PaymentRefCode` (payment
notifications) or `BasketRefCode` (invoice and basket notifications).

## Features

- ✅ PHP 8.1+ compatible
- ✅ Builder pattern for clean configuration
- ✅ Laravel & Symfony integration
- ✅ Interceptor pipeline (request/response hooks)
- ✅ Rich error hierarchy (Auth, Forbidden, NotFound, RateLimit, Redirect, Validation)
- ✅ Automatic retry for GET requests (exponential backoff & Retry-After header)
- ✅ Redirects are never followed (`OdealRedirectException`)
- ✅ Configurable timeout
- ✅ Optional idempotency key header for POST/PUT/PATCH (`enableIdempotencyKey`, off by default; the Ödeal API does not use it yet,
  a basket is identified by its unique `basketReferenceCode`)
- ✅ Client-side input validation
- ✅ Sensitive data masking in debug logs
- ✅ Response unwrapping (`{"result": [...]}` → auto-extract)
- ✅ Circuit breaker pattern
- ✅ Request Logger middleware
- ✅ PSR-3 logging support
- ✅ Guzzle HTTP client
- ✅ Webhook verification helpers (`X-ODEAL-REQUEST-KEY`, Basic Auth)
- ✅ Discounted basket helpers (`OdealAllowances`)
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
### ProfileResource

| Method | Description |
|:-------|:------------|
| `getProfileDetails()` | Kayıtlı terminal entegrasyon profil bilgilerinizi (profil tipi, IP listesi, terminal ID vb.) getirir. |
| `updateProfile()` | Terminal entegrasyon profil tipini günceller. Profil tipi, hangi sepet akışının kabul edileceğini belirler. |

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
