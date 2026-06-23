<?php

declare(strict_types=1);

namespace Odeal\Sdk;

use Exception;

/**
 * Odeal SDK'dan fırlatılan tüm exception'ların temel sınıfı.
 */
class OdealException extends Exception {}

/**
 * API isteklerinde oluşan hataları temsil eder.
 */
class OdealApiException extends OdealException
{
    public function __construct(
        string $message,
        private int $statusCode,
        private ?string $responseBody = null
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int { return $this->statusCode; }
    public function getResponseBody(): ?string { return $this->responseBody; }
}

/** Kimlik doğrulama hatası (401). */
class OdealAuthenticationException extends OdealApiException
{
    public function __construct(string $message = 'Authentication failed.', ?string $responseBody = null) {
        parent::__construct($message, 401, $responseBody);
    }
}

/** Yetkilendirme hatası (403). */
class OdealForbiddenException extends OdealApiException
{
    public function __construct(string $message = 'Access denied.', ?string $responseBody = null) {
        parent::__construct($message, 403, $responseBody);
    }
}

/** Kaynak bulunamadı (404). */
class OdealNotFoundException extends OdealApiException
{
    public function __construct(string $message = 'Resource not found.', ?string $responseBody = null) {
        parent::__construct($message, 404, $responseBody);
    }
}

/** Rate limit aşıldı (429). */
class OdealRateLimitException extends OdealApiException
{
    public function __construct(
        string $message = 'Rate limit exceeded.',
        ?string $responseBody = null,
        private ?int $retryAfterSeconds = null
    ) {
        parent::__construct($message, 429, $responseBody);
    }

    public function getRetryAfterSeconds(): ?int { return $this->retryAfterSeconds; }
}

/** Client-side validation hatası. */
class OdealValidationException extends OdealException
{
    /** @var string[] */
    private array $errors;

    /** @param string[] $errors */
    public function __construct(string $message, array $errors = []) {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /** @return string[] */
    public function getErrors(): array { return $this->errors; }
}

/** Ağ bağlantı hatası. */
class OdealNetworkException extends OdealException {}

/** İstek zaman aşımı hatası. */
class OdealTimeoutException extends OdealNetworkException
{
    public function __construct(string $message = 'The request timed out.') {
        parent::__construct($message);
    }
}