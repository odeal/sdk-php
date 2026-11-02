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
    private const AGENT = 'OdealSdkPhpClient/0.2.0';
    
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
        ?string $baseUrl = null,
        ?string $expect = null,
        ?string $model = null
    ): mixed {
        $this->prepareBody($body);

        
                $this->guardCircuitBreaker();
                

        // Çağrı başına adres önceliklidir; null ya da boşsa yapılandırmanın etkin adresi: sandbox modu, açık baseUrl,
        // ortam (sözleşme §11, §12; C25, C26).
        $url = $this->buildUrl(($baseUrl ?? '') !== '' ? $baseUrl : $this->config->getEffectiveBaseUrl(), $path, $queryParams);
        $headers = $this->buildHeaders($method, $headerParams);

        if ($this->config->debugMode) {
            $this->logCurl($method, $url, $headers, $body);
        }

        return $this->executeWithRetry($method, $url, $headers, $body, $expect, $model);
    }

    // ==================== Async Send API ====================

    /**
     * Asenkron HTTP isteği gönderir. Guzzle Promise döner.
     *
     * @return PromiseInterface Promise; resolve olduğunda yanıt gövdesi (string) döner, $model verildiyse model nesnesi,
     *         model listesi ya da null (boş yanıt).
     */
    protected function sendAsync(
        string $method,
        string $path,
        mixed $body = null,
        ?array $queryParams = null,
        ?array $headerParams = null,
        ?string $baseUrl = null,
        ?string $expect = null,
        ?string $model = null
    ): PromiseInterface {
        $this->prepareBody($body);

        
                $this->guardCircuitBreaker();
                

        // Çağrı başına adres önceliklidir; null ya da boşsa yapılandırmanın etkin adresi: sandbox modu, açık baseUrl,
        // ortam (sözleşme §11, §12; C25, C26).
        $url = $this->buildUrl(($baseUrl ?? '') !== '' ? $baseUrl : $this->config->getEffectiveBaseUrl(), $path, $queryParams);
        $headers = $this->buildHeaders($method, $headerParams);

        if ($this->config->debugMode) {
            $this->logCurl($method, $url, $headers, $body);
        }

        $asyncOptions = [
            'headers' => $headers,
            'http_errors' => false,
            'timeout' => $this->config->timeout,
            // Yönlendirme izlenmez (davranış sözleşmesi §1). İstek seviyesindeki bu ayar, dışarıdan verilen
            // Guzzle istemcisinin ayarını da ezer; 3xx aşağıda hataya dönüşür.
            'allow_redirects' => false,
        ];
        if ($body instanceof MultipartBody) {
            unset($asyncOptions['headers']['Content-Type']);
            $asyncOptions['multipart'] = $body->toGuzzleParts();
        } else {
            $asyncOptions['json'] = $body;
        }

        
                // Kanca (async, NEW-08): onBeforeRequest gönderimden önce çağrılır. Hatası olduğu gibi ve senkron yayılır
                // (devre kesici ve validasyon hatalarıyla aynı); istek gönderilmez (sözleşme §10).
                $this->invokeBeforeInterceptors($method, $url, $headers, $body);
                
        $startTime = microtime(true);

        return $this->httpClient->requestAsync($method, $url, $asyncOptions)->then(
            function ($response) use ($method, $url, $headers, $body, $startTime, $expect, $model) {
                $statusCode = $response->getStatusCode();
                $content = $response->getBody()->getContents();

                
                                // Devre-kıran (senkron yolla aynı): 5xx/429 = başarısızlık; diğer (2xx/3xx/4xx) = sunucu yanıt verdi.
                                // Kayıt kancadan önce ve yanıt başına bir kez.
                                if ($this->circuitBreaker) {
                                    if ($statusCode >= 500 || $statusCode === 429) {
                                        $this->circuitBreaker->recordFailure();
                                    } else {
                                        $this->circuitBreaker->recordSuccess();
                                    }
                                }
                                

                
                                // onAfterResponse: hatası olduğu gibi yayılır (promise bu hatayla reddedilir). Bu then'in rejection
                                // callback'i yalnız transport hatalarını alır; kanca hatası ağ hatası sayılmaz.
                                $this->invokeAfterInterceptors($method, $url, $headers, $body, $statusCode, $content, (microtime(true) - $startTime) * 1000);
                                

                if ($statusCode >= 300) {
                    throw $this->mapApiException($statusCode, $content);
                }

                if ($expect !== null) {
                    // Yanıt biçimi (sözleşme §8): uyumsuzlukta promise OdealApiException ile reddedilir.
                    $this->assertResponseShape($expect, $statusCode, $content);
                }
                if ($model !== null) {
                    return $this->convertResponse($content, $model, $expect === 'list', $statusCode);
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

        
                // Idempotency Key: yalnız enableIdempotencyKey açıksa (varsayılan kapalı, sözleşme §3), POST/PUT/PATCH'te.
                if ($this->config->enableIdempotencyKey && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
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

    private function executeWithRetry(string $method, string $url, array $headers, mixed $body, ?string $expect = null, ?string $model = null): mixed
    {
        // Retry politikası (davranış sözleşmesi §1): yalnız GET yeniden denenir. GET dışındaki yöntemlerde
        // (HEAD ve OPTIONS dahil) SDK isteği tekrar göndermez; sunucu ilk isteği işlemiş olabilir.
        $maxRetries = strtoupper($method) === 'GET' ? $this->config->maxRetryCount : 0;
        $currentTry = 0;

        while (true) {
            
                        // Kancalar ağ katmanı try'ının dışında: kanca hatası, türü ne olursa olsun (GuzzleException dahil) olduğu
                        // gibi yayılır; ağ hatası sayılmaz, istek tekrar gönderilmez (sözleşme §10).
                        $this->invokeBeforeInterceptors($method, $url, $headers, $body);
                        

            $startTime = microtime(true);
            $options = [
                'headers' => $headers,
                'timeout' => $this->config->timeout,
                'http_errors' => false,
                // Yönlendirme izlenmez (davranış sözleşmesi §1). İstek seviyesindeki bu ayar, dışarıdan verilen
                // Guzzle istemcisinin ayarını da ezer; 3xx aşağıda hataya dönüşür.
                'allow_redirects' => false,
            ];
            if ($body instanceof MultipartBody) {
                // multipart/form-data: Guzzle boundary'li Content-Type'ı kendi ayarlar.
                unset($options['headers']['Content-Type']);
                $options['multipart'] = $body->toGuzzleParts();
            } else {
                $options['json'] = $body;
            }

            // Ağ katmanı: yalnız gönderim bu try içinde. Buradaki hata transport hatasıdır ve yalnız GET'te tekrar
            // denenir; kancalar ve yanıt işleme bu bloğun dışındadır.
            try {
                $response = $this->httpClient->request($method, $url, $options);
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
            $durationMs = (microtime(true) - $startTime) * 1000;

            $statusCode = $response->getStatusCode();
            $content = $response->getBody()->getContents();

            $isRetryableFailure = ($statusCode >= 500 || $statusCode === 429);

            
                        // Devre-kıran kaydı kancadan önce ve yanıt başına bir kez yapılır (sözleşme §6): 5xx/429 = başarısızlık;
                        // diğer (2xx/3xx/4xx) = sunucu yanıt verdi. Kanca hata fırlatsa da kayıt yapılmış olur.
                        if ($this->circuitBreaker) {
                            if ($isRetryableFailure) {
                                $this->circuitBreaker->recordFailure();
                            } else {
                                $this->circuitBreaker->recordSuccess();
                            }
                        }
                        

            
                        // Kanca hatası olduğu gibi yayılır; istek tekrar gönderilmez (sözleşme §10).
                        $this->invokeAfterInterceptors($method, $url, $headers, $body, $statusCode, $content, $durationMs);
                        

            if ($isRetryableFailure && $currentTry < $maxRetries) {
                $currentTry++;
                
                                $delay = $this->calculateRetryDelay($currentTry, $response);
                                
                $this->debugLog("Request failed with $statusCode. Retrying in " . ($delay / 1000000) . "s. Attempt $currentTry of $maxRetries", 'warning');
                usleep($delay);
                continue;
            }

            if ($statusCode >= 300) {
                $this->debugLog("API Error: $statusCode - " . $this->bodyForLog($content), 'error');
                throw $this->mapApiException($statusCode, $content);
            }

            if ($expect !== null) {
                // Yanıt biçimi (sözleşme §8): beklenmeyen biçim OdealApiException olur; tekrar denenmez.
                $this->assertResponseShape($expect, $statusCode, $content);
            }
            if ($model !== null) {
                return $this->convertResponse($content, $model, $expect === 'list', $statusCode);
            }

            return $content;
        }
    }

    // Zengin hata hiyerarşisi: status → spesifik exception tipi (senkron ve asenkron yol ortak).
    // Yönlendirme izlenmez (davranış sözleşmesi §1): 3xx durum kodu korunarak OdealRedirectException olur.
    private function mapApiException(int $statusCode, string $content): OdealApiException
    {
        if ($statusCode >= 300 && $statusCode < 400) {
            return new OdealRedirectException(
                "Sunucu yönlendirme döndürdü (HTTP $statusCode). SDK yönlendirme izlemez; baseUrl'yi kontrol edin.",
                $statusCode,
                $content
            );
        }

        return match ($statusCode) {
            401 => new OdealAuthenticationException(responseBody: $content),
            403 => new OdealForbiddenException(responseBody: $content),
            404 => new OdealNotFoundException(responseBody: $content),
            429 => new OdealRateLimitException(responseBody: $content),
            default => new OdealApiException("API Error: $statusCode", $statusCode, $content),
        };
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
        // Liste metodu boş gövdede ve JSON null'da boş liste döner (sözleşme §8).
        if ($data === null) return $isArray ? [] : null;

        if ($isArray && is_array($data) && array_key_exists('result', $data)) {
            $data = $data['result'] ?? [];
        }

        if ($isArray && is_array($data)) {
            return array_map(fn($item) => $this->mapObject($item, $className), $data);
        }

        return $this->mapObject($data, $className);
    }

    /**
     * Başarılı (2xx) yanıtın biçimini doğrular (davranış sözleşmesi §8, NEW-10).
     *
     * $expect 'list': {"result":[...]} (ek alanlar serbest) ya da doğrudan dizi; boş gövde, JSON null, {} ve
     * {"result":null} boş listedir (şemada result zorunlu değil); elemanlar JSON nesnesi olmalı. $expect 'object': JSON
     * nesnesi ya da null. Uyumsuzlukta OdealApiException: durum kodu yanıtın HTTP kodu, gövde istisnada tutulur,
     * iletiye yazılmaz. JSON nesnesi ile dizinin ayrılabilmesi için nesneler stdClass olarak çözülür.
     */
    protected function assertResponseShape(string $expect, int $statusCode, string $content): void
    {
        if (trim($content) === '') {
            return;
        }
        try {
            $data = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw $this->shapeError($statusCode, $content, 'yanıt geçerli JSON değil');
        }
        if ($data === null) {
            return;
        }

        if ($expect === 'object') {
            if (!($data instanceof \stdClass)) {
                throw $this->shapeError($statusCode, $content, 'nesne bekleniyordu; ' . $this->jsonKind($data) . ' geldi');
            }
            return;
        }

        if ($data instanceof \stdClass) {
            if (property_exists($data, 'result')) {
                $items = $data->result;
                if ($items === null) {
                    return;
                }
                if (!is_array($items)) {
                    throw $this->shapeError($statusCode, $content, "liste bekleniyordu; 'result' alanı " . $this->jsonKind($items));
                }
            } elseif (count(get_object_vars($data)) === 0) {
                return;
            } else {
                throw $this->shapeError($statusCode, $content, "liste bekleniyordu; 'result' alanı yok");
            }
        } elseif (is_array($data)) {
            $items = $data;
        } else {
            throw $this->shapeError($statusCode, $content, 'liste bekleniyordu; ' . $this->jsonKind($data) . ' geldi');
        }

        foreach ($items as $index => $item) {
            if (!($item instanceof \stdClass)) {
                throw $this->shapeError($statusCode, $content, "liste elemanı nesne değil ($index: " . $this->jsonKind($item) . ')');
            }
        }
    }

    private function jsonKind(mixed $value): string
    {
        return match (true) {
            $value instanceof \stdClass => 'nesne',
            is_array($value) => 'dizi',
            is_string($value) => 'metin',
            is_bool($value) => 'mantıksal',
            is_int($value), is_float($value) => 'sayı',
            $value === null => 'null',
            default => gettype($value),
        };
    }

    private function shapeError(int $statusCode, string $content, string $detail): OdealApiException
    {
        return new OdealApiException("Beklenmeyen yanıt biçimi (HTTP $statusCode): $detail.", $statusCode, $content);
    }

    /**
     * Biçimi doğrulanmış yanıtı model sınıfına dönüştürür (sözleşme §8). İç içe alanda yanlış tip (ör. nesne yerine
     * sayı) tipli özelliğe atamada ham TypeError üretir; bu hata HTTP kodu ve gövdeyle OdealApiException'a çevrilir.
     * Sınır yalnız dönüşümü kapsar: gönderim, kancalar ve devre kesici dışarıdadır. İç içe tipler ayrıca şemaya göre
     * denetlenmez; nesne olmayan iç içe liste elemanı boş modele çevrilir (mapObject).
     */
    private function convertResponse(string $content, string $model, bool $isList, int $statusCode): mixed
    {
        try {
            return $this->deserialize($content, $model, $isList);
        } catch (\TypeError|\ValueError $e) {
            throw $this->shapeError($statusCode, $content, 'yanıt beklenen tipe dönüştürülemedi');
        }
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

    /**
     * Yanıt gövdesinin log biçimi (sözleşme §8, SEC-08): hata ayıklama modu kapalıyken gövde loglanmaz, yalnız
     * uzunluğu yazılır; açıkken hassas veri maskeleme açıksa maskelenerek yazılır.
     */
    private function bodyForLog(string $content): string
    {
        if (!$this->config->debugMode) {
            return '(gövde loglanmadı, uzunluk ' . strlen($content) . ')';
        }
        
                if ($this->config->maskSensitiveData) {
                    return OdealSanitizer::sanitizeJson($content) ?? $content;
                }
                
        return $content;
    }

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
                        // stripos PHP 8.2 öncesinde yerele bağlı; başlık adı yalnız ASCII küçültülür.
                        $lk = strtr($k, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
                        if ($this->config->maskSensitiveData && (str_contains($lk, 'secret') || str_contains($lk, 'key') || str_contains($lk, 'authorization'))) {
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
