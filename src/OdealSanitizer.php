<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * Hassas verileri maskeleyen yardımcı sınıf.
 * Interceptor body verileri ve debug loglarında hassas verilerin sızmasını engeller.
 */
class OdealSanitizer
{
    private const SENSITIVE_FIELDS = [
        'password', 'cvv', 'cvc', 'cardNumber', 'card_number', 'pan',
        'expiryDate', 'expiry_date', 'securityCode', 'security_code',
        'secretKey', 'secret_key', 'token', 'accessToken', 'access_token',
        'refreshToken', 'refresh_token', 'authorization',
        'tckn', 'tcKimlikNo', 'tcKimlik', 'identityNumber', 'nationalId',
        'iban', 'phone', 'phoneNumber', 'telephone', 'gsm',
        'email', 'eMail', 'mail', 'address', 'adres',
        'gsmNumber', 'basicAuthPassword', 'odealRequestKey', 'merchantKey', 'externalDeviceKey', 'taxNumber', 'apiKey',
    ];

    /** Adında bu parçalardan biri geçen her alan da maskelenir (büyük/küçük harf duyarsız; ör. basicAuthPassword). */
    private const SENSITIVE_FIELD_PARTS = ['password', 'secret', 'token'];

    private const SENSITIVE_HEADER_KEYWORDS = ['secret', 'key', 'authorization', 'token', 'cookie', 'session'];

    /**
     * JSON string içindeki hassas alanları maskeler.
     *
     * JSON ağacı parse edilerek iç içe nesneler, diziler ve string olmayan değerler
     * (sayı, boolean) dahil tüm hassas alanlar maskelenir. Geçerli JSON değilse regex
     * tabanlı güvenli yedeğe düşülür.
     */
    public static function sanitizeJson(?string $json): ?string
    {
        if ($json === null || $json === '') {
            return $json;
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return self::sanitizeJsonRegex($json);
        }

        $encoded = json_encode(self::maskValue($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded === false ? self::sanitizeJsonRegex($json) : $encoded;
    }

    /**
     * JSON değerini recursive olarak gezerek hassas alanları maskeler.
     *
     * @param mixed $value
     * @return mixed
     */
    private static function maskValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = (is_string($key) && self::isSensitiveField($key))
                ? '***'
                : self::maskValue($item);
        }
        return $out;
    }

    /**
     * Alan adı hassas mı: listede varsa ya da adında password/secret/token geçiyorsa.
     */
    private static function isSensitiveField(string $name): bool
    {
        static $sensitive = null;
        if ($sensitive === null) {
            $sensitive = array_flip(array_map([self::class, 'asciiLower'], self::SENSITIVE_FIELDS));
        }
        $lower = self::asciiLower($name);
        if (isset($sensitive[$lower])) {
            return true;
        }
        foreach (self::SENSITIVE_FIELD_PARTS as $part) {
            if (str_contains($lower, $part)) {
                return true;
            }
        }
        return false;
    }

    /** strtolower PHP 8.2 öncesinde yerele bağlı (Türkçe yerelde "I" farklı küçülebilir); yalnız ASCII çevrilir. */
    private static function asciiLower(string $s): string
    {
        return strtr($s, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    }

    /**
     * Parse edilemeyen içerik için regex tabanlı yedek maskeleme.
     */
    private static function sanitizeJsonRegex(string $json): string
    {
        $result = $json;
        foreach (self::SENSITIVE_FIELDS as $field) {
            $pattern = '/("' . preg_quote($field, '/') . '"\s*:\s*")[^"]*(")/i';
            $result = preg_replace($pattern, '${1}***${2}', $result);
        }
        // Adında password/secret/token geçen alanlar (ör. "basicAuthPassword").
        return preg_replace('/("[^"]*(?:password|secret|token)[^"]*"\s*:\s*")[^"]*(")/i', '${1}***${2}', $result);
    }

    /**
     * Header array'indeki hassas değerleri maskeler.
     */
    public static function sanitizeHeaders(?array $headers): array
    {
        if ($headers === null) {
            return [];
        }

        $sanitized = [];
        foreach ($headers as $key => $value) {
            $lower = self::asciiLower($key);
            $isSensitive = false;
            foreach (self::SENSITIVE_HEADER_KEYWORDS as $keyword) {
                if (str_contains($lower, $keyword)) {
                    $isSensitive = true;
                    break;
                }
            }
            $sanitized[$key] = $isSensitive ? '***' : $value;
        }
        return $sanitized;
    }
}
