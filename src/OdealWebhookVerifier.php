<?php

declare(strict_types=1);

namespace Odeal\Sdk;

/**
 * Ödeal'in gönderdiği geri bildirim (webhook) isteklerini doğrulama yardımcıları.
 *
 * Ödeal, yapılandırmanızda tanımlıysa isteğe `X-ODEAL-REQUEST-KEY` başlığını (`odealRequestKey` değeri) koyar;
 * Ödeal entegrasyon ekibiyle Basic Auth da tanımlanabilir (entegrasyon dokümanı, "Geri Bildirim"). Doğrulama
 * isteğe bağlıdır: yapılandırmanıza uyan yöntemi kullanın. Karşılaştırmalar sabit zamanlıdır.
 *
 * Kullanım:
 *   $requestKey = $_SERVER['HTTP_X_ODEAL_REQUEST_KEY'] ?? null;
 *   if (!OdealWebhookVerifier::verifyRequestKey($requestKey, 'your-odeal-request-key')) {
 *       http_response_code(401);
 *       exit;
 *   }
 *   http_response_code(200); // 2xx ile hızlı yanıt verin, ağır işi sonra yapın
 */
final class OdealWebhookVerifier
{
    /** Ödeal'in istek anahtarını taşıyan başlık. */
    public const REQUEST_KEY_HEADER = 'X-ODEAL-REQUEST-KEY';

    /** Basic Auth bilgisini taşıyan başlık. */
    public const AUTHORIZATION_HEADER = 'Authorization';

    /**
     * `X-ODEAL-REQUEST-KEY` başlığının değeri yapılandırmadaki `odealRequestKey` ile aynı mı.
     * Başlık ya da beklenen anahtar boşsa false döner.
     */
    public static function verifyRequestKey(?string $receivedKey, ?string $expectedKey): bool
    {
        if ($receivedKey === null || $receivedKey === '' || $expectedKey === null || $expectedKey === '') {
            return false;
        }
        return self::fixedTimeEquals($receivedKey, $expectedKey);
    }

    /**
     * `Authorization: Basic ...` başlığı verilen kullanıcı adı ve parolayla eşleşiyor mu.
     * Başlık yoksa, Basic değilse, base64 geçersizse ya da beklenen bilgiler boşsa false döner.
     */
    public static function verifyBasicAuth(?string $authorizationHeader, ?string $username, ?string $password): bool
    {
        if ($authorizationHeader === null || $authorizationHeader === '' || $username === null || $username === ''
            || $password === null || $password === '') {
            return false;
        }
        $value = trim($authorizationHeader);
        $space = strpos($value, ' ');
        if ($space === false || $space === 0
            || strtr(substr($value, 0, $space), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') !== 'basic') {
            return false;
        }
        $received = base64_decode(trim(substr($value, $space + 1)), true);
        if ($received === false) {
            return false;
        }
        return self::fixedTimeEquals($received, $username . ':' . $password);
    }

    /** Uzunluk farkı da sızmasın diye iki değerin SHA-256 özeti sabit zamanlı karşılaştırılır. */
    private static function fixedTimeEquals(string $a, string $b): bool
    {
        return hash_equals(hash('sha256', $b, true), hash('sha256', $a, true));
    }
}
