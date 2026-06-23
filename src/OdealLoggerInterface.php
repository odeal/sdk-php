<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * SDK-internal logger interface.
 *
 * PSR-3 uyumludur — Monolog vb. logger'lar doğrudan kullanılabilir.
 * psr/log paketine bağımlılık gerektirmez.
 *
 * @example
 * ```php
 * // Basit adapter
 * $logger = new class implements OdealLoggerInterface {
 *     public function debug(string $message, array $context = []): void { error_log("[DEBUG] $message"); }
 *     public function info(string $message, array $context = []): void { error_log("[INFO] $message"); }
 *     public function warning(string $message, array $context = []): void { error_log("[WARN] $message"); }
 *     public function error(string $message, array $context = []): void { error_log("[ERROR] $message"); }
 * };
 *
 * // Monolog doğrudan geçilebilir (PSR-3 uyumlu)
 * $config = new OdealConfig(secretKey: 'sk_xxx', merchantKey: 'mk_xxx', logger: $monolog);
 * ```
 */
interface OdealLoggerInterface
{
    public function debug(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
}
