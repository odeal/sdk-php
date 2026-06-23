<?php

namespace Odeal\Sdk;

/**
 * SDK varsayılan değerleri ve HTTP durum kodu sabitleri.
 */
final class OdealDefaults
{
    // --- Timeout & Retry ---
    public const TIMEOUT_SEC = 30;
    public const MAX_RETRY_COUNT = 3;
    public const BACKOFF_BASE_MS = 500;

    // --- Circuit Breaker ---
    public const CIRCUIT_BREAKER_THRESHOLD = 5;
    public const CIRCUIT_BREAKER_RESET_MS = 60000;

    // --- HTTP Status Codes ---
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_TOO_MANY_REQUESTS = 429;

    public static function isServerError(int $statusCode): bool
    {
        return $statusCode >= 500;
    }

    public static function isRetryable(int $statusCode): bool
    {
        return self::isServerError($statusCode) || $statusCode === self::HTTP_TOO_MANY_REQUESTS;
    }
}
