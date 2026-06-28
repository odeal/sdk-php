<?php

declare(strict_types=1);

namespace Odeal\Sdk;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseResource
{
    private const AGENT = 'OdealSdkPhpClient/0.1.6';
    
        private ?OdealCircuitBreaker $circuitBreaker = null;
        

    public function __construct(
        protected GuzzleClient $httpClient,
        protected OdealConfig $config
    ) {
        
                if ($this->config->circuitBreakerEnabled) {
                    $this->circuitBreaker = new OdealCircuitBreaker(
                        $this->config->circuitBreakerThreshold,
                        $this->config->circuitBreakerResetMs
                    );
                }
                
    }

    // ==================== Public Send API ====================

    protected function send(
        string $method,
        string $path,
        mixed $body = null,
        ?array $queryParams = null,
        ?array $headerParams = null,
        ?string $baseUrl = null
    ): string {
        $this->prepareBody($body);

        
                $this->guardCircuitBreaker();
                

        $url = $this->buildUrl($baseUrl ?? $this->config->baseUrl, $path, $queryParams);
        $headers = $this->buildHeaders($method, $headerParams);

        if ($this->config->debugMode) {
            $this->logCurl($method, $url, $headers, $body);
        }

        return $this->executeWithRetry($method, $url, $headers, $body);
    }

    // ==================== Async Send API ====================

    /**
     * Asenkron HTTP isteği gönderir. Guzzle Promise döner.
     *
     * @return PromiseInterface Promise, resolve olduğunda response body string döner.
     */
    protected function sendAsync(
        string $method,
        string $path,
        mixed $body = null,
        ?array $queryParams = null,
        ?array $headerParams = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $this->prepareBody($body);

        
                $this->guardCircuitBreaker();
                

        $url = $this->buildUrl($baseUrl ?? $this->config->baseUrl, $path, $queryParams);
        $headers = $this->buildHeaders($method, $headerParams);

        if ($this->config->debugMode) {
            $this->logCurl($method, $url, $headers, $body);
        }

        $asyncOptions = [
            'headers' => $headers,
            'http_errors' => false,
            'timeout' => $this->config->timeout,
        ];
        if ($body instanceof MultipartBody) {
            unset($asyncOptions['headers']['Content-Type']);
            $asyncOptions['multipart'] = $body->toGuzzleParts();
        } else {
            $asyncOptions['json'] = $body;
        }

        return $this->httpClient->requestAsync($method, $url, $asyncOptions)->then(
            function ($response) {
                $statusCode = $response->getStatusCode();
                $content = $response->getBody()->getContents();

                if ($statusCode >= 400) {
                    
                                        if ($this->circuitBreaker) {
                                            $this->circuitBreaker->recordFailure();
                                        }
                                        
                    // Zengin hata hiyerarşisi: status → spesifik exception tipi
                    throw match ($statusCode) {
                        401 => new OdealAuthenticationException(responseBody: $content),
                        403 => new OdealForbiddenException(responseBody: $content),
                        404 => new OdealNotFoundException(responseBody: $content),
                        429 => new OdealRateLimitException(responseBody: $content),
                        default => new OdealApiException("API Error: $statusCode", $statusCode, $content),
                    };
                }

                
                                if ($this->circuitBreaker) {
                                    $this->circuitBreaker->recordSuccess();
                                }
                                

                return $content;
            },
            function ($reason) {
                
                                if ($this->circuitBreaker) {
                                    $this->circuitBreaker->recordFailure();
                                }
                                
                $message = $reason instanceof \Exception ? $reason->getMessage() : (string) $reason;
                throw new OdealApiException("Connection Error: " . $message, 0);
            }
        );
    }

    // ==================== URL Building ====================

    private function buildUrl(string $baseUrl, string $path, ?array $queryParams): string
    {
        // Slash normalize (C#/TS/Python/Java ile tutarlı): baseUrl sonu ve path başı tek '/'.
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        if (!empty($queryParams)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($queryParams);
        }
        return $url;
    }

    // ==================== Headers ====================

    private function buildHeaders(string $method, ?array $headerParams): array
    {
        // Auth header'lar (X-ODEAL-SECRET-KEY, X-ODEAL-MERCHANT-KEY) swagger-declared
        // params olarak Resource sınıfından $headerParams ile gelir.
        $headers = array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-ODEAL-AGENT' => self::AGENT,
        ], $headerParams ?? []);

        // OAuth2 / Bearer: accessToken ayarlıysa Authorization header ekle (boşsa eklenmez)
        if (!empty($this->config->accessToken)) {
            $headers['Authorization'] = 'Bearer ' . $this->config->accessToken;
        }

        
                // Idempotency Key: POST/PUT/PATCH isteklerinde çift işlem koruması
                if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
                    $idemHeader = 'X-Odeal-Idempotency-Key';
                    if (!isset($headers[$idemHeader])) {
                        $headers[$idemHeader] = bin2hex(random_bytes(16));
                    }
                }
                

        return $headers;
    }

    // ==================== Body Preparation ====================

    private function prepareBody(mixed $body): void
    {
        // multipart gövdesi swagger modeli değildir; default-doldurma/validasyon uygulanmaz.
        if ($body === null || $body instanceof MultipartBody) return;

        $this->applyClientDefaults($body);

        
                if (!$this->config->skipClientValidation) {
                    $this->validateModel($body);
                }
                
    }

    
        // ==================== Circuit Breaker ====================
    
        private function guardCircuitBreaker(): void
        {
            if ($this->circuitBreaker && !$this->circuitBreaker->allowRequest()) {
                throw new OdealCircuitOpenException();
            }
        }
        

    // ==================== Retry Execution ====================

    private function executeWithRetry(string $method, string $url, array $headers, mixed $body): string
    {
        $maxRetries = $this->config->maxRetryCount;
        $currentTry = 0;

        while (true) {
            try {
                
                                $this->invokeBeforeInterceptors($method, $url, $headers, $body);
                                

                $startTime = microtime(true);
                $options = [
                    'headers' => $headers,
                    'timeout' => $this->config->timeout,
                    'http_errors' => false,
                ];
                if ($body instanceof MultipartBody) {
                    // multipart/form-data: Guzzle boundary'li Content-Type'ı kendi ayarlar.
                    unset($options['headers']['Content-Type']);
                    $options['multipart'] = $body->toGuzzleParts();
                } else {
                    $options['json'] = $body;
                }
                $response = $this->httpClient->request($method, $url, $options);
                $durationMs = (microtime(true) - $startTime) * 1000;

                $statusCode = $response->getStatusCode();
                $content = $response->getBody()->getContents();

                
                                $this->invokeAfterInterceptors($method, $url, $headers, $body, $statusCode, $content, $durationMs);
                                

                $isRetryableFailure = ($statusCode >= 500 || $statusCode === 429);

                
                                // Devre-kıran: 5xx/429 = başarısızlık; diğer (2xx/4xx) = sunucu yanıt verdi (başarı sayılır).
                                if ($this->circuitBreaker) {
                                    if ($isRetryableFailure) {
                                        $this->circuitBreaker->recordFailure();
                                    } else {
                                        $this->circuitBreaker->recordSuccess();
                                    }
                                }
                                

                if ($isRetryableFailure && $currentTry < $maxRetries) {
                    $currentTry++;
                    
                                        $delay = $this->calculateRetryDelay($currentTry, $response);
                                        
                    $this->debugLog("Request failed with $statusCode. Retrying in " . ($delay / 1000000) . "s. Attempt $currentTry of $maxRetries", 'warning');
                    usleep($delay);
                    continue;
                }

                if ($statusCode >= 400) {
                    $this->debugLog("API Error: $statusCode - $content", 'error');
                    // Zengin hata hiyerarşisi: status → spesifik exception tipi
                    throw match ($statusCode) {
                        401 => new OdealAuthenticationException(responseBody: $content),
                        403 => new OdealForbiddenException(responseBody: $content),
                        404 => new OdealNotFoundException(responseBody: $content),
                        429 => new OdealRateLimitException(responseBody: $content),
                        default => new OdealApiException("API Error: $statusCode", $statusCode, $content),
                    };
                }

                return $content;

            } catch (GuzzleException $e) {
                
                                if ($this->circuitBreaker) {
                                    $this->circuitBreaker->recordFailure();
                                }
                                

                if ($currentTry < $maxRetries) {
                    $currentTry++;
                    $delay = (int)(pow(2, $currentTry) * 500 * 1000);
                    $this->debugLog("Network error: " . $e->getMessage() . ". Retrying in " . ($delay / 1000000) . "s. Attempt $currentTry of $maxRetries", 'warning');
                    usleep($delay);
                    continue;
                }

                $this->debugLog("Connection Error: " . $e->getMessage(), 'error');
                throw new OdealApiException("Connection Error: " . $e->getMessage(), 0);
            }
        }
    }

    
        private function calculateRetryDelay(int $attempt, $response): int
        {
            $delayStr = $response->getHeaderLine('Retry-After');
            return $delayStr
                ? ((int)$delayStr * 1000000)
                : (int)(pow(2, $attempt) * 500 * 1000);
        }
        

    
        // ==================== Interceptors ====================
    
        private function invokeBeforeInterceptors(string $method, string $url, array $headers, mixed $body): void
        {
            $bodyJson = $body ? json_encode($body) : null;
            
                $safeBody = $this->config->maskSensitiveData ? OdealSanitizer::sanitizeJson($bodyJson) : $bodyJson;
                $safeHeaders = $this->config->maskSensitiveData ? OdealSanitizer::sanitizeHeaders($headers) : $headers;
                
    
            foreach ($this->config->interceptors as $interceptor) {
                $interceptor->onBeforeRequest(new RequestContext(
                    method: $method,
                    url: $url,
                    headers: $safeHeaders,
                    body: $safeBody
                ));
            }
        }
    
        private function invokeAfterInterceptors(string $method, string $url, array $headers, mixed $body, int $statusCode, string $content, float $durationMs): void
        {
            $bodyJson = $body ? json_encode($body) : null;
            
                $safeBody = $this->config->maskSensitiveData ? OdealSanitizer::sanitizeJson($bodyJson) : $bodyJson;
                $safeContent = $this->config->maskSensitiveData ? OdealSanitizer::sanitizeJson($content) : $content;
                $safeHeaders = $this->config->maskSensitiveData ? OdealSanitizer::sanitizeHeaders($headers) : $headers;
                
    
            foreach ($this->config->interceptors as $interceptor) {
                $interceptor->onAfterResponse(new ResponseContext(
                    statusCode: $statusCode,
                    body: $safeContent,
                    durationMs: $durationMs,
                    request: new RequestContext(method: $method, url: $url, headers: $safeHeaders)
                ));
            }
        }
    
        // ==================== Validation ====================
    
        private function validateModel(object $model): void
        {
            $errors = [];
            $reflection = new ReflectionClass($model);
    
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
                $attrs = $prop->getAttributes(OdealClientDefault::class);
                if (!empty($attrs)) {
                    $value = $prop->getValue($model);
                    if ($value === null || $value === '') {
                        $errors[] = $prop->getName() . ' alanı zorunludur.';
                    }
                }
    
                // Nested model validation (recursive)
                $value = $prop->getValue($model);
                if (is_object($value) && !($value instanceof \BackedEnum)) {
                    try {
                        $this->validateModel($value);
                    } catch (OdealValidationException $e) {
                        foreach ($e->getErrors() as $nestedError) {
                            $errors[] = $prop->getName() . '.' . $nestedError;
                        }
                    }
                }
            }
    
            if (!empty($errors)) {
                $this->debugLog('Validation Failed: ' . implode(', ', $errors), 'warning');
                throw new OdealValidationException('Validation hatası: ' . implode('; ', $errors), $errors);
            }
        }
        

    // ==================== Response Processing ====================

    /**
     * JSON yanıtını ilgili Model sınıfına (veya listesine) dönüştürür.
     */
    protected function deserialize(string $json, string $className, bool $isArray = false): mixed
    {
        $data = json_decode($json, true);
        if ($data === null) return null;

        if ($isArray && isset($data['result'])) {
            $data = $data['result'];
        }

        if ($isArray && is_array($data)) {
            return array_map(fn($item) => $this->mapObject($item, $className), $data);
        }

        return $this->mapObject($data, $className);
    }

    protected function applyConfigDefaults(array &$params, array $paramDefinitions): void
    {
        foreach ($paramDefinitions as $name => $def) {
            if (!isset($params[$name]) || $params[$name] === null) {
                $configProp = $def['configProp'] ?? null;
                if ($configProp && property_exists($this->config, $configProp)) {
                    $params[$name] = $this->config->{$configProp};
                }
            }
        }
    }

    // ==================== Object Mapping ====================

    private function mapObject(mixed $data, string $className): object
    {
        if (!is_array($data)) {
            return new $className();
        }

        // oneOf sarmalayıcı: discriminator'a göre doğru varyanta map et.
        if (defined("$className::ONEOF_VARIANTS")) {
            $wrapper = new $className();
            $discProp = $className::ONEOF_DISCRIMINATOR;
            $disc = $discProp !== null ? ($data[$discProp] ?? null) : null;
            $wrapper->discriminator = $disc;
            if ($disc !== null && isset($className::ONEOF_VARIANTS[$disc])) {
                $variantClass = $className::ONEOF_VARIANTS[$disc];
                $variantProp = $className::ONEOF_PROPERTY[$disc];
                $wrapper->{$variantProp} = $this->mapObject($data, $variantClass);
            }
            return $wrapper;
        }

        $instance = new $className();
        $reflection = new ReflectionClass($instance);

        foreach ($data as $key => $value) {
            $propName = lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key))));

            if (property_exists($instance, $propName)) {
                // Array Type Mapping
                if (defined("$className::ARRAY_TYPES") && isset($className::ARRAY_TYPES[$propName]) && is_array($value)) {
                    $itemType = $className::ARRAY_TYPES[$propName];
                    if (class_exists($itemType)) {
                        $instance->{$propName} = array_map(fn($item) => $this->mapObject($item, $itemType), $value);
                        continue;
                    }
                }

                // Enum Type Mapping
                if (defined("$className::ENUM_TYPES") && isset($className::ENUM_TYPES[$propName]) && $value !== null) {
                    $enumType = $className::ENUM_TYPES[$propName];
                    if (enum_exists($enumType)) {
                        $instance->{$propName} = $enumType::tryFrom($value) ?? $value;
                        continue;
                    }
                }

                $property = $reflection->getProperty($propName);
                $type = $property->getType();
                $typeName = $type ? $type->getName() : null;

                if ($type && !$type->isBuiltin() && is_array($value)) {
                    if (class_exists($typeName)) {
                        $instance->{$propName} = $this->mapObject($value, $typeName);
                        continue;
                    }
                }

                if ($typeName === 'object' && is_array($value)) {
                    $instance->{$propName} = (object)$value;
                } else {
                    $instance->{$propName} = $value;
                }
            }
        }
        return $instance;
    }

    // ==================== Client Defaults ====================

    private function applyClientDefaults(object $model): void
    {
        $reflection = new ReflectionClass($model);
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $attrs = $prop->getAttributes(OdealClientDefault::class);
            if (!empty($attrs) && $prop->getValue($model) === null) {
                $configProp = $prop->getName();
                if (property_exists($this->config, $configProp)) {
                    $prop->setValue($model, $this->config->{$configProp});
                }
            }
        }
    }

    // ==================== Debug Logging ====================

    protected function debugLog(string $message, string $level = 'debug'): void
    {
        
                if ($this->config->logger && method_exists($this->config->logger, $level)) {
                    $this->config->logger->$level("[ODEAL SDK] " . $message);
                } elseif ($this->config->debugMode || $level === 'error' || $level === 'warning') {
                    error_log("[ODEAL SDK " . strtoupper($level) . "] " . $message);
                }
                
    }

    private function logCurl(string $method, string $url, array $headers, mixed $body): void
    {
        $curl = "curl -X $method '$url'";
        foreach ($headers as $k => $v) {
            
                        $safeVal = $v;
                        if ($this->config->maskSensitiveData && (stripos($k, 'secret') !== false || stripos($k, 'key') !== false || stripos($k, 'authorization') !== false)) {
                            $safeVal = "***";
                        }
                        $curl .= " -H '$k: $safeVal'";
                        
        }
        if ($body) {
            $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE);
            
                        if ($this->config->maskSensitiveData) {
                            $jsonBody = OdealSanitizer::sanitizeJson((string)$jsonBody);
                        }
                        
            $curl .= " -d '" . addslashes((string)$jsonBody) . "'";
        }
        $this->debugLog($curl, 'info');
    }
}
