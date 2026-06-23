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
    ];

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
        static $sensitive = null;
        if ($sensitive === null) {
            $sensitive = array_flip(array_map('strtolower', self::SENSITIVE_FIELDS));
        }

        if (!is_array($value)) {
            return $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = (is_string($key) && isset($sensitive[strtolower($key)]))
                ? '***'
                : self::maskValue($item);
        }
        return $out;
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
        return $result;
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
            $lower = strtolower($key);
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
