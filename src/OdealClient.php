<?php

declare(strict_types=1);

namespace Odeal\Sdk;

use GuzzleHttp\Client as GuzzleClient;
use Odeal\Sdk\Models;
use Odeal\Sdk\Resources;

/**
 * Odeal API ana istemci sinifi.
 * Tum API islemlerine dogrudan erisim saglar.
 */
class OdealClient
{
    private OdealConfig $config;
    private GuzzleClient $httpClient;
    private Resources\BasketResource $_basket;
    private Resources\PaymentResource $_payment;
    private Resources\ConfigurationResource $_configuration;
    private Resources\UnitResource $_unit;
    private Resources\ReportResource $_report;
    private Resources\ProfileResource $_profile;

    /**
     * @param OdealConfig $config SDK yapilandirma ayarlari.
     * @param GuzzleClient|null $httpClient Harici bir Guzzle client kullanmak istenirse.
     */
    public function __construct(OdealConfig $config, ?GuzzleClient $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? new GuzzleClient([
            'base_uri' => $config->getEffectiveBaseUrl(),
            'timeout'  => 30.0,
        ]);
        $this->_basket = new Resources\BasketResource($this->httpClient, $config);
        $this->_payment = new Resources\PaymentResource($this->httpClient, $config);
        $this->_configuration = new Resources\ConfigurationResource($this->httpClient, $config);
        $this->_unit = new Resources\UnitResource($this->httpClient, $config);
        $this->_report = new Resources\ReportResource($this->httpClient, $config);
        $this->_profile = new Resources\ProfileResource($this->httpClient, $config);
    }

    /**
     * Yapilandirma nesnesini doner.
     * @return OdealConfig
     */
    public function getConfig(): OdealConfig
    {
        return $this->config;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RESOURCE ACCESSORS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Basket API islemlerine dogrudan erisim.
     * @return Resources\BasketResource
     */
    public function basket(): Resources\BasketResource
    {
        return $this->_basket;
    }

    /**
     * Payment API islemlerine dogrudan erisim.
     * @return Resources\PaymentResource
     */
    public function payment(): Resources\PaymentResource
    {
        return $this->_payment;
    }

    /**
     * Configuration API islemlerine dogrudan erisim.
     * @return Resources\ConfigurationResource
     */
    public function configuration(): Resources\ConfigurationResource
    {
        return $this->_configuration;
    }

    /**
     * Unit API islemlerine dogrudan erisim.
     * @return Resources\UnitResource
     */
    public function unit(): Resources\UnitResource
    {
        return $this->_unit;
    }

    /**
     * Report API islemlerine dogrudan erisim.
     * @return Resources\ReportResource
     */
    public function report(): Resources\ReportResource
    {
        return $this->_report;
    }

    /**
     * Profile API islemlerine dogrudan erisim.
     * @return Resources\ProfileResource
     */
    public function profile(): Resources\ProfileResource
    {
        return $this->_profile;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // FACADE METHODS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Standart ürün satışı. Müşteri Bireysel veya Kurumsal olabilir. 'items' alanı zorunludur.
     *
     * @param \Odeal\Sdk\Models\BasketRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse|null
     */
    public function createSimpleBasket(
        \Odeal\Sdk\Models\BasketRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\BasketResponse {
        return $this->_basket->createSimpleBasket(
            $request,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Avans tahsilatı. Müşteri Bireysel veya Kurumsal olabilir. 'items' gönderilmez. `basketType` ADVANCE olmalıdır.
     *
     * @param \Odeal\Sdk\Models\BasketRequestAdvance $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse|null
     */
    public function createAdvanceBasket(
        \Odeal\Sdk\Models\BasketRequestAdvance $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\BasketResponse {
        return $this->_basket->createAdvanceBasket(
            $request,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Cari hesap tahsilatı. Müşteri Kurumsal olmalıdır. `basketType` CURRENT_ACCOUNT olmalıdır.
     *
     * @param \Odeal\Sdk\Models\BasketRequestCurrentAccount $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse|null
     */
    public function createCurrentAccountBasket(
        \Odeal\Sdk\Models\BasketRequestCurrentAccount $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\BasketResponse {
        return $this->_basket->createCurrentAccountBasket(
            $request,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Yemek kartı işlemleri. `receiptInfo` ve içindeki `foodCardBrandId` zorunludur.
     *
     * @param \Odeal\Sdk\Models\BasketRequestFoodCard $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse|null
     */
    public function createFoodCardBasket(
        \Odeal\Sdk\Models\BasketRequestFoodCard $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\BasketResponse {
        return $this->_basket->createFoodCardBasket(
            $request,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Sepet Listele
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $externalDeviceKey 
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketListResponse|null
     */
    public function listBaskets(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $externalDeviceKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\BasketListResponse {
        return $this->_basket->listBaskets(
            
            $secretKey,
            $merchantKey,
            $externalDeviceKey,
            $baseUrl
        );
    }

    /**
     * Sepet Sil
     *
     * @param string $referenceCode Silinecek sepetin referans kodu
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return void
     */
    public function deleteBasket(
        string $referenceCode,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): void {
        $this->_basket->deleteBasket(
            
            $referenceCode,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Tüm Sepetleri Sil
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return void
     */
    public function deleteAllBaskets(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): void {
        $this->_basket->deleteAllBaskets(
            
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Ödeme İptali
     *
     * @param \Odeal\Sdk\Models\CancelPaymentRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\CancelPaymentResponse|null
     */
    public function cancelPayment(
        \Odeal\Sdk\Models\CancelPaymentRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\CancelPaymentResponse {
        return $this->_payment->cancelPayment(
            $request,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Konfigürasyon Kaydet
     *
     * @param \Odeal\Sdk\Models\ConfigurationRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return void
     */
    public function saveConfiguration(
        \Odeal\Sdk\Models\ConfigurationRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): void {
        $this->_configuration->saveConfiguration(
            $request,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Konfigürasyon Getir
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\ConfigurationResponse|null
     */
    public function getConfiguration(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\ConfigurationResponse {
        return $this->_configuration->getConfiguration(
            
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Birimleri Listele
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\Unit[]
     */
    public function listUnits(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): array {
        return $this->_unit->listUnits(
            
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * İşlem Raporu
     *
     * @param string $beginDate 
     * @param string $endDate 
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $externalDeviceKey 
     * @param string $basketReferenceCode 
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\TransactionReport[]
     */
    public function getTransactionReport(
        string $beginDate,
        string $endDate,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $externalDeviceKey = null,
        ?string $basketReferenceCode = null,
        ?string $baseUrl = null
    ): array {
        return $this->_report->getTransactionReport(
            
            $beginDate,
            $endDate,
            $secretKey,
            $merchantKey,
            $externalDeviceKey,
            $basketReferenceCode,
            $baseUrl
        );
    }

    /**
     * Kayıtlı terminal entegrasyon profil bilgilerinizi (profil tipi, IP listesi, terminal ID vb.) getirir.
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\ProfileDetailsResponse|null
     */
    public function getProfileDetails(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): ?\Odeal\Sdk\Models\ProfileDetailsResponse {
        return $this->_profile->getProfileDetails(
            
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }

    /**
     * Terminal entegrasyon profil tipini günceller. Profil tipi, hangi sepet akışının kabul edileceğini belirler.
     *
     * @param \Odeal\Sdk\Models\ProfileUpdateRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return void
     */
    public function updateProfile(
        \Odeal\Sdk\Models\ProfileUpdateRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): void {
        $this->_profile->updateProfile(
            $request,
            $secretKey,
            $merchantKey,
            $baseUrl
        );
    }
}