<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * Odeal Webhook İmza Doğrulama Sınıfı.
 *
 * Kullanım:
 *   $isValid = OdealWebhookVerifier::verifySignature(
 *       file_get_contents('php://input'),
 *       $_SERVER['HTTP_X_ODEAL_SIGNATURE'] ?? '',
 *       'your-webhook-secret'
 *   );
 */
class OdealWebhookVerifier
{
    public const SIGNATURE_HEADER = 'X-Odeal-Signature';
    public const TIMESTAMP_HEADER = 'X-Odeal-Timestamp';
    public const DEFAULT_TOLERANCE_SECONDS = 300; // 5 dakika

    /**
     * Webhook imzasını doğrular.
     */
    public static function verifySignature(string $payload, string $signature, string $webhookSecret): bool
    {
        if (empty($payload) || empty($signature) || empty($webhookSecret)) {
            return false;
        }

        $expected = self::computeSignature($payload, $webhookSecret);
        return hash_equals($expected, $signature);
    }

    /**
     * Birden fazla secret ile doğrulama (secret rotation desteği).
     * @param string[] $webhookSecrets
     */
    public static function verifySignatureMulti(string $payload, string $signature, array $webhookSecrets): bool
    {
        if (empty($payload) || empty($signature) || empty($webhookSecrets)) {
            return false;
        }
        foreach ($webhookSecrets as $secret) {
            if (!empty($secret) && self::verifySignature($payload, $signature, $secret)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Timestamp kontrolü ile doğrulama (replay attack koruması).
     */
    public static function verifySignatureWithTimestamp(
        string $payload,
        string $signature,
        string $timestamp,
        string $webhookSecret,
        int $toleranceSeconds = self::DEFAULT_TOLERANCE_SECONDS
    ): bool {
        if (!is_numeric($timestamp)) {
            return false;
        }

        $diff = abs(time() - (int) $timestamp);
        if ($diff > $toleranceSeconds) {
            return false;
        }

        $signedPayload = "{$timestamp}.{$payload}";
        return self::verifySignature($signedPayload, $signature, $webhookSecret);
    }

    /**
     * Birden fazla secret + timestamp doğrulama (rotation + replay koruması).
     * @param string[] $webhookSecrets
     */
    public static function verifySignatureWithTimestampMulti(
        string $payload,
        string $signature,
        string $timestamp,
        array $webhookSecrets,
        int $toleranceSeconds = self::DEFAULT_TOLERANCE_SECONDS
    ): bool {
        if (!is_numeric($timestamp)) {
            return false;
        }
        $diff = abs(time() - (int) $timestamp);
        if ($diff > $toleranceSeconds) {
            return false;
        }
        $signedPayload = "{$timestamp}.{$payload}";
        return self::verifySignatureMulti($signedPayload, $signature, $webhookSecrets);
    }

    /**
     * HMAC-SHA256 imza hesaplar.
     */
    public static function computeSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }
}
