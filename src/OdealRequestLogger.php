<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * Hazır Odeal Request Logger middleware'i.
 * 
 * Interceptor pipeline üzerine inşa edilmiştir. Tüm HTTP isteklerini ve
 * yanıtlarını yapılandırılabilir şekilde loglar.
 * 
 * Örnek kullanım:
 * ```php
 * $logger = new OdealRequestLogger(
 *     level: 'info',
 *     maskFields: ['cvv', 'password']
 * );
 * 
 * $client = new OdealClient(new OdealConfig(
 *     secretKey: 'sk_xxx',
 *     interceptors: [$logger]
 * ));
 * ```
 */
class OdealRequestLogger implements OdealInterceptorInterface
{
    private const DEFAULT_MASK_FIELDS = [
        'password', 'cvv', 'cvc', 'cardNumber', 'card_number', 'pan',
        'expiryDate', 'expiry_date', 'securityCode', 'security_code',
        'secretKey', 'secret_key', 'token', 'accessToken', 'access_token',
        'refreshToken', 'refresh_token', 'authorization',
        'tckn', 'tcKimlikNo', 'tcKimlik', 'identityNumber', 'nationalId',
        'iban', 'phone', 'phoneNumber', 'telephone', 'gsm',
        'email', 'eMail', 'mail', 'address', 'adres',
    ];

    /** @var string[] */
    private array $maskFields;

    public function __construct(
        private string $level = 'info',
        private float $minDurationMs = 0,
        ?array $maskFields = null,
        private bool $logBody = true,
        private bool $logResponseBody = false,
        private ?\Closure $logger = null,
    ) {
        $this->maskFields = $maskFields ?? self::DEFAULT_MASK_FIELDS;
    }

    public function onBeforeRequest(RequestContext $context): void
    {
        $parts = ["→ {$context->method} {$context->url}"];
        if ($this->logBody && $context->body !== null) {
            $parts[] = "  Body: " . $this->maskSensitive($context->body);
        }
        $this->log(implode("\n", $parts), 'info');
    }

    public function onAfterResponse(ResponseContext $context): void
    {
        if ($context->durationMs < $this->minDurationMs) return;

        $status = $context->statusCode;
        $level = $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info');
        $req = $context->request;
        $parts = [
            "← {$status} " . ($req?->method ?? '') . " " . ($req?->url ?? '') . " ({$context->durationMs}ms)"
        ];
        if ($this->logResponseBody && $context->body !== null) {
            $parts[] = "  Body: " . $this->maskSensitive(substr($context->body, 0, 500));
        }
        $this->log(implode("\n", $parts), $level);
    }

    private function log(string $message, string $level): void
    {
        $formatted = "[ODEAL " . strtoupper($level) . "] " . $message;
        if ($this->logger !== null) {
            ($this->logger)($formatted, $level);
        } else {
            error_log($formatted);
        }
    }

    private function maskSensitive(string $text): string
    {
        $result = $text;
        foreach ($this->maskFields as $field) {
            $result = preg_replace(
                '/("' . preg_quote($field, '/') . '"\s*:\s*")[^"]+(")/i',
                '${1}***$2',
                $result
            ) ?? $result;
        }
        return $result;
    }
}
