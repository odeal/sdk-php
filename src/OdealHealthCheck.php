<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * API bağlantı durumunu kontrol eder.
 *
 * Kullanım:
 *   $healthCheck = new OdealHealthCheck($config);
 *   $result = $healthCheck->ping();
 *   echo $result->isHealthy() ? '✓' : '✗';
 */
class OdealHealthCheck
{
    private OdealConfig $config;

    public function __construct(OdealConfig $config)
    {
        $this->config = $config;
    }

    /**
     * API'ye basit bir GET isteği göndererek bağlantıyı test eder.
     */
    public function ping(): HealthCheckResult
    {
        $start = microtime(true);
        // Kimlik doğrulaması istemeyen GET /healthcheck; anahtar gönderilmez. Adres yapılandırmanın etkin adresi (§12).
        $baseUrl = $this->config->getEffectiveBaseUrl();
        try {
            $ch = curl_init($baseUrl . '/healthcheck');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
                // Yönlendirme izlenmez; 3xx sağlıksız sayılır.
                CURLOPT_FOLLOWLOCATION => false,
            ]);

            curl_exec($ch);
            $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $latencyMs = (int) ((microtime(true) - $start) * 1000);

            if (!empty($error)) {
                return new HealthCheckResult(false, 0, $latencyMs, $baseUrl, $error);
            }

            return new HealthCheckResult(
                $statusCode >= 200 && $statusCode < 300,
                $statusCode,
                $latencyMs,
                $baseUrl
            );
        } catch (\Throwable $e) {
            $latencyMs = (int) ((microtime(true) - $start) * 1000);
            return new HealthCheckResult(false, 0, $latencyMs, $baseUrl, $e->getMessage());
        }
    }
}

/**
 * Health check sonucu.
 */
class HealthCheckResult
{
    public function __construct(
        private readonly bool $healthy,
        private readonly int $statusCode,
        private readonly int $latencyMs,
        private readonly string $baseUrl,
        private readonly ?string $errorMessage = null,
    ) {}

    public function isHealthy(): bool { return $this->healthy; }
    public function getStatusCode(): int { return $this->statusCode; }
    public function getLatencyMs(): int { return $this->latencyMs; }
    public function getBaseUrl(): string { return $this->baseUrl; }
    public function getErrorMessage(): ?string { return $this->errorMessage; }

    public function __toString(): string
    {
        if ($this->healthy) {
            return "✓ Healthy ({$this->latencyMs}ms) {$this->baseUrl}";
        }
        $msg = $this->errorMessage ?? "HTTP {$this->statusCode}";
        return "✗ Unhealthy ({$this->latencyMs}ms) {$this->baseUrl} — {$msg}";
    }
}
