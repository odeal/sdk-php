<?php

declare(strict_types=1);

namespace Odeal\Sdk\Resources;

use Odeal\Sdk\BaseResource;
use Odeal\Sdk\OdealConfig;
use Odeal\Sdk\Models;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Profile API islemleri.
 */
class ProfileResource extends BaseResource
{
    public function __construct(GuzzleClient $httpClient, OdealConfig $config)
    {
        parent::__construct($httpClient, $config);
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
        $path = '/profile/details';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->send(
            'Get',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            'object',
            \Odeal\Sdk\Models\ProfileDetailsResponse::class
        );
    }

    /**
     * [ASYNC] Kayıtlı terminal entegrasyon profil bilgilerinizi (profil tipi, IP listesi, terminal ID vb.) getirir.
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\ProfileDetailsResponse ya da null (boş yanıt) döner.
     */
    public function getProfileDetailsAsync(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/profile/details';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->sendAsync(
            'Get',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            'object',
            \Odeal\Sdk\Models\ProfileDetailsResponse::class
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
        $path = '/profile/update';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        $this->send(
            'Patch',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            null
        );
    }

    /**
     * [ASYNC] Terminal entegrasyon profil tipini günceller. Profil tipi, hangi sepet akışının kabul edileceğini belirler.
     *
     * @param \Odeal\Sdk\Models\ProfileUpdateRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda void döner.
     */
    public function updateProfileAsync(
        \Odeal\Sdk\Models\ProfileUpdateRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/profile/update';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->sendAsync(
            'Patch',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            null
        );
    }
}
