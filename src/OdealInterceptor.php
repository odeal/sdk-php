<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * İstek bağlamı — interceptor'a gönderilen istek bilgileri.
 */
class RequestContext
{
    public function __construct(
        public string $method = '',
        public string $url = '',
        public array $headers = [],
        public ?string $body = null,
        public array $metadata = []
    ) {}
}

/**
 * Yanıt bağlamı — interceptor'a gönderilen yanıt bilgileri.
 */
class ResponseContext
{
    public function __construct(
        public int $statusCode = 0,
        public array $headers = [],
        public ?string $body = null,
        public float $durationMs = 0.0,
        public ?RequestContext $request = null,
        public array $metadata = []
    ) {}
}

/**
 * HTTP istek/yanıt pipeline'ına araya girmek için kullanılan interceptor interface'i.
 * 
 * Örnek kullanım:
 * ```php
 * class LoggingInterceptor implements OdealInterceptorInterface
 * {
 *     public function onBeforeRequest(RequestContext $context): void
 *     {
 *         echo "[REQUEST] {$context->method} {$context->url}\n";
 *     }
 * 
 *     public function onAfterResponse(ResponseContext $context): void
 *     {
 *         echo "[RESPONSE] {$context->statusCode} ({$context->durationMs}ms)\n";
 *     }
 * }
 * ```
 */
interface OdealInterceptorInterface
{
    /**
     * HTTP isteği gönderilmeden önce çağrılır.
     */
    public function onBeforeRequest(RequestContext $context): void;

    /**
     * HTTP yanıtı alındıktan sonra çağrılır.
     */
    public function onAfterResponse(ResponseContext $context): void;
}
