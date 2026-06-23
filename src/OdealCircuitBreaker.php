<?php

declare(strict_types=1);

namespace Odeal\Sdk;

enum OdealCircuitState: string
{
    case Closed = 'closed';
    case Open = 'open';
    case HalfOpen = 'half_open';
}

/**
 * Circuit Breaker - art arda hata alindiginda istekleri otomatik durdurur.
 * CLOSED -> OPEN -> HALF_OPEN -> CLOSED
 */
class OdealCircuitBreaker
{
    private OdealCircuitState $state = OdealCircuitState::Closed;
    private int $failureCount = 0;
    private int $lastFailureAtMs = 0;
    private bool $halfOpenProbeInFlight = false;
    private string $lockFile;

    public function __construct(
        private int $failureThreshold = 5,
        private int $resetTimeoutMs = 60000,
        ?string $lockFile = null
    ) {
        $this->lockFile = $lockFile ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'odeal-sdk-circuit-breaker.lock';
    }

    public function getCurrentState(): string
    {
        return $this->withLock(function (): string {
            if ($this->state === OdealCircuitState::Open && $this->isResetElapsed()) {
                return OdealCircuitState::HalfOpen->value;
            }
            return $this->state->value;
        });
    }

    /**
     * İsteğin geçip geçemeyeceğini kontrol eder. HALF_OPEN durumunda yalnızca TEK bir
     * probe (test) isteğine izin verilir; probe sonuçlanana kadar diğerleri reddedilir.
     */
    public function allowRequest(): bool
    {
        return $this->withLock(function (): bool {
            // OPEN -> HALF_OPEN: reset süresi dolduysa tek bir probe denemesine kapı aç.
            if ($this->state === OdealCircuitState::Open && $this->isResetElapsed()) {
                $this->state = OdealCircuitState::HalfOpen;
                $this->halfOpenProbeInFlight = false;
            }

            if ($this->state === OdealCircuitState::Closed) {
                return true;
            }
            if ($this->state === OdealCircuitState::HalfOpen) {
                if ($this->halfOpenProbeInFlight) {
                    return false;
                }
                $this->halfOpenProbeInFlight = true; // probe izni bu isteğe verildi
                return true;
            }
            return false; // Open
        });
    }

    public function recordSuccess(): void
    {
        $this->withLock(function (): void {
            $this->failureCount = 0;
            $this->state = OdealCircuitState::Closed;
            $this->lastFailureAtMs = 0;
            $this->halfOpenProbeInFlight = false;
        });
    }

    public function recordFailure(): void
    {
        $this->withLock(function (): void {
            $this->lastFailureAtMs = self::nowMs();

            if ($this->state === OdealCircuitState::HalfOpen) {
                // Probe başarısız -> doğrudan tekrar OPEN.
                $this->state = OdealCircuitState::Open;
                $this->halfOpenProbeInFlight = false;
                return;
            }

            $this->failureCount++;
            if ($this->failureCount >= $this->failureThreshold) {
                $this->state = OdealCircuitState::Open;
            }
        });
    }

    public function reset(): void
    {
        $this->withLock(function (): void {
            $this->failureCount = 0;
            $this->state = OdealCircuitState::Closed;
            $this->lastFailureAtMs = 0;
            $this->halfOpenProbeInFlight = false;
        });
    }

    private function isResetElapsed(): bool
    {
        return $this->lastFailureAtMs !== 0
            && (self::nowMs() - $this->lastFailureAtMs) >= $this->resetTimeoutMs;
    }

    private function withLock(callable $operation): mixed
    {
        $handle = @fopen($this->lockFile, 'c');
        if ($handle === false) {
            return $operation();
        }

        $locked = false;
        try {
            $locked = flock($handle, LOCK_EX);
            return $operation();
        } finally {
            if ($locked) {
                flock($handle, LOCK_UN);
            }
            fclose($handle);
        }
    }

    private static function nowMs(): int
    {
        return intdiv(hrtime(true), 1_000_000);
    }
}

class OdealCircuitOpenException extends OdealException
{
    public function __construct(string $message = 'Circuit breaker is open. Requests are temporarily blocked.')
    {
        parent::__construct($message);
    }
}
