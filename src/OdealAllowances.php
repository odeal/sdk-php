<?php

declare(strict_types=1);

namespace Odeal\Sdk;

use Odeal\Sdk\Enums\AllowanceSubType;
use Odeal\Sdk\Models\Allowance;
use Odeal\Sdk\Models\BasketPrice;
use Odeal\Sdk\Models\Item;

/**
 * İskontolu sepet yardımcıları: ara toplam iskontosunu satırlara dağıtır ve sepet özetindeki
 * `totalAllowance` ile `paidAmount` alanlarını yazar.
 *
 * Kurallar entegrasyon dokümanındaki "İskontolu Sepet Aktar" sayfasından gelir: tutarlar KDV dahildir; ara toplam
 * iskontosu satırların KDV dahil tutarlarına oranla dağıtılır, her pay 2 haneye yuvarlanır ve kalan kuruş son satıra
 * verilir; sepette SUB iskonto varsa her satırda bulunmalıdır; iskonto sonrası sepet toplamı 1 TL'den az olamaz.
 * Hesap kuruş üzerinden tam sayıyla yapılır.
 * Yalnız tutar bazlı iskontolar (`isRateBased = false`) desteklenir; oran bazlı iskontonun tutarını Ödeal hesaplar.
 * Hata olursa hiçbir alan değişmez. Ödeme seçenekleri değiştirilmez: `paymentOptions` toplamı `paidAmount`
 * olmalıdır.
 *
 * Örnek:
 * ```php
 * $price = new BasketPrice();
 * $price->grossPrice = 405.00;
 * OdealAllowances::applySubtotalAllowance($price, $items, 40.00);
 * // Satırlar: SUB 23,70 / 10,37 / 5,93. $price->totalAllowance = 40.0, $price->paidAmount = 365.0
 * ```
 */
final class OdealAllowances
{
    private const PREFIX = 'İskonto: ';
    // Backend'de order 32 bit tam sayıdır; PHP int'i 64 bit olduğundan aralık ayrıca denetlenir.
    private const INT32_MIN = -2147483648;
    private const INT32_MAX = 2147483647;

    private function __construct()
    {
    }

    /**
     * Ara toplam iskontosunu (KDV dahil) satırlara dağıtır: her satıra bir SUB iskonto ekler, sonra sepet özetini
     * applyAllowanceTotals() ile aynı kuralla yazar. Her çağrı her satıra yeni bir SUB iskonto ekler.
     * Her satır ayrı bir Item nesnesi olmalıdır. Satırın allowances dizisi, eklenen iskontoyla birlikte yeni bir diziyle
     * değiştirilir; özellik dışarıdaki bir diziye referansla bağlıysa bağ koparılır ve o dizi değişmez. Eklenen
     * iskontonun sırası satırdaki en büyük order (yoksa 0) + 1'dir; mevcut order değerleri 32 bit tam sayı olmalıdır.
     *
     * @param BasketPrice|null $price Sepet özeti; grossPrice sepetin iskontosuz, KDV dahil toplamıdır.
     * @param Item[]|null $items Sepet satırları.
     * @param float $amount Ara toplam iskontosu (TL, KDV dahil, en çok 2 ondalık).
     * @throws OdealValidationException Girdi kurallara uymuyorsa. Hiçbir alan değişmez.
     */
    public static function applySubtotalAllowance(?BasketPrice $price, ?array $items, float $amount): void
    {
        if ($price === null) {
            throw self::fail('price (sepet özeti) zorunludur.');
        }
        if ($items === null || count($items) === 0) {
            throw self::fail('items boş olamaz.');
        }
        if (!($amount > 0) || is_infinite($amount)) {
            throw self::fail('tutar sıfırdan büyük olmalıdır.');
        }
        if (!self::hasAtMostTwoDecimals($amount)) {
            throw self::fail('tutar en çok 2 ondalık basamak içermelidir.');
        }
        $amountCents = self::cents($amount);
        $items = array_values($items);
        $count = count($items);

        $bases = [];
        $orders = [];
        $seen = [];
        $totalBase = 0.0;
        $existingTotal = 0;
        for ($i = 0; $i < $count; $i++) {
            $item = $items[$i];
            $n = $i + 1;
            if (!isset($item->product->price->grossPrice) || !isset($item->quantity) || !($item->quantity > 0)) {
                throw self::fail("satır $n için ürün, birim fiyat ya da adet eksik.");
            }
            // Aynı nesne iki satırda olursa eklenen iskonto iki satıra birden yazılır ve gövde özetle ayrışır.
            $id = spl_object_id($item);
            if (isset($seen[$id])) {
                throw self::fail("satır $n, satır {$seen[$id]} ile aynı Item nesnesi; her satır ayrı bir nesne olmalıdır.");
            }
            $seen[$id] = $n;
            $existing = self::sumAmountCents($item, $n, $unused);
            $orders[$i] = self::nextOrder($item, $n);
            $lineBase = $item->quantity * $item->product->price->grossPrice - $existing / 100.0;
            if (!($lineBase > 0)) {
                throw self::fail("satır $n için iskonto uygulanabilecek tutar kalmadı; SUB iskonto her satırda bulunmalıdır.");
            }
            $bases[$i] = $lineBase;
            $totalBase += $lineBase;
            $existingTotal += $existing;
        }
        if ($amountCents / 100.0 > $totalBase) {
            throw self::fail('tutar, iskonto uygulanabilecek toplamı aşıyor.');
        }

        $shares = [];
        $assigned = 0;
        for ($i = 0; $i < $count - 1; $i++) {
            $shares[$i] = (int) floor($amountCents * $bases[$i] / $totalBase + 0.5);
            $assigned += $shares[$i];
        }
        $shares[$count - 1] = $amountCents - $assigned;
        for ($i = 0; $i < $count; $i++) {
            $n = $i + 1;
            if ($shares[$i] < 1) {
                throw self::fail("satır $n payı 0,01 TL'den küçük kalıyor; SUB iskonto her satırda pozitif olmalıdır.");
            }
            if ($shares[$i] > $bases[$i] * 100 + 1e-6) {
                throw self::fail("satır $n payı satır tutarını aşıyor.");
            }
        }

        // Toplamlar yazmadan önce hesaplanır; hata olursa hiçbir alan değişmez.
        [$total, $paid] = self::totals($price, $existingTotal + $amountCents);

        for ($i = 0; $i < $count; $i++) {
            $item = $items[$i];
            // Dizi değer olarak kopyalanır. Özellik dışarıdaki bir diziye referansla (=&) bağlıysa bağ yazmadan önce
            // koparılır; yoksa yeni dizi o diziye ve ona bağlı öteki satırlara da yazılırdı. Böylece her satır yalnız
            // kendi payını alır ve dış dizi değişmez.
            $list = $item->allowances ?? [];
            $allowance = new Allowance();
            $allowance->price = $shares[$i] / 100.0;
            $allowance->isRateBased = false;
            $allowance->subType = AllowanceSubType::SUB;
            $allowance->order = $orders[$i];
            $list[] = $allowance;
            unset($item->allowances);
            $item->allowances = $list;
        }
        $price->totalAllowance = $total;
        $price->paidAmount = $paid;
    }

    /**
     * Sepet özetindeki `totalAllowance` (satır iskontolarının toplamı) ve `paidAmount`
     * (`grossPrice − totalAllowance`) alanlarını satırlardaki tutar bazlı iskontolardan yazar.
     * Hiçbir satırda iskonto yoksa hiçbir alana dokunmaz.
     *
     * @param BasketPrice|null $price Sepet özeti.
     * @param Item[]|null $items Sepet satırları.
     * @throws OdealValidationException Girdi kurallara uymuyorsa. Hiçbir alan değişmez.
     */
    public static function applyAllowanceTotals(?BasketPrice $price, ?array $items): void
    {
        if ($price === null) {
            throw self::fail('price (sepet özeti) zorunludur.');
        }
        if ($items === null) {
            return;
        }
        $sum = 0;
        $count = 0;
        $i = 0;
        foreach (array_values($items) as $item) {
            $sum += self::sumAmountCents($item, $i + 1, $c);
            $count += $c;
            $i++;
        }
        if ($count === 0) {
            return;
        }
        [$total, $paid] = self::totals($price, $sum);
        $price->totalAllowance = $total;
        $price->paidAmount = $paid;
    }

    /** @return array{0: float, 1: float} */
    private static function totals(BasketPrice $price, int $allowanceCents): array
    {
        if (!isset($price->grossPrice)) {
            throw self::fail('grossPrice zorunludur.');
        }
        $gross = self::cents($price->grossPrice);
        if ($allowanceCents > $gross) {
            throw self::fail('iskonto toplamı sepet tutarını aşıyor.');
        }
        if ($gross - $allowanceCents < 100) {
            throw self::fail("iskonto sonrası sepet toplamı 1 TL'den az olamaz (Ödeal hata kodu 2304).");
        }
        return [$allowanceCents / 100.0, ($gross - $allowanceCents) / 100.0];
    }

    /** Eklenecek iskontonun sırası: satırdaki en büyük order (yoksa 0) + 1. Mevcut order 32 bit tam sayı olmalıdır. */
    private static function nextOrder(object $item, int $n): int
    {
        $max = 0;
        foreach ($item->allowances ?? [] as $a) {
            if ($a === null) {
                continue;
            }
            $o = $a->order ?? null;
            if ($o === null) {
                continue;
            }
            if (!is_int($o) || $o < self::INT32_MIN || $o > self::INT32_MAX) {
                throw self::fail("satır $n iskontosunda order 32 bit tam sayı olmalıdır.");
            }
            if ($o > $max) {
                $max = $o;
            }
        }
        if ($max === self::INT32_MAX) {
            throw self::fail("satır $n için yeni iskontonun order değeri 2147483647 sınırını aşıyor.");
        }
        return $max + 1;
    }

    /** Satırdaki tutar bazlı iskontoların kuruş toplamı. Oran bazlı iskonto ya da geçersiz tutar hata verir. */
    private static function sumAmountCents(mixed $item, int $n, ?int &$count): int
    {
        $count = 0;
        $sum = 0;
        $list = is_object($item) ? ($item->allowances ?? null) : null;
        if ($list === null) {
            return 0;
        }
        foreach ($list as $a) {
            if ($a === null) {
                continue;
            }
            if (!isset($a->isRateBased)) {
                throw self::fail("satır $n iskontosunda isRateBased zorunludur.");
            }
            if ($a->isRateBased) {
                throw self::fail("satır $n oran bazlı iskonto içeriyor; tutarını Ödeal hesapladığı için yardımcı desteklemez.");
            }
            $p = $a->price ?? null;
            if (!(is_int($p) || is_float($p)) || !($p > 0) || is_infinite((float) $p)) {
                throw self::fail("satır $n iskontosunda price sıfırdan büyük olmalıdır.");
            }
            $p = (float) $p;
            if (!self::hasAtMostTwoDecimals($p)) {
                throw self::fail("satır $n iskonto tutarı en çok 2 ondalık basamak içermelidir.");
            }
            $sum += self::cents($p);
            $count++;
        }
        return $sum;
    }

    private static function cents(float $x): int
    {
        return (int) floor($x * 100 + 0.5);
    }

    private static function hasAtMostTwoDecimals(float $x): bool
    {
        return abs($x * 100 - floor($x * 100 + 0.5)) <= 1e-6;
    }

    private static function fail(string $message): OdealValidationException
    {
        $text = self::PREFIX . $message;
        return new OdealValidationException($text, [$text]);
    }
}
