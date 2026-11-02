<?php

declare(strict_types=1);

namespace Odeal\Sdk\Resources;

use Odeal\Sdk\BaseResource;
use Odeal\Sdk\OdealConfig;
use Odeal\Sdk\Models;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Configuration API islemleri.
 */
class ConfigurationResource extends BaseResource
{
    public function __construct(GuzzleClient $httpClient, OdealConfig $config)
    {
        parent::__construct($httpClient, $config);
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
        $path = '/configuration';

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
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            null
        );
    }

    /**
     * [ASYNC] Konfigürasyon Kaydet
     *
     * @param \Odeal\Sdk\Models\ConfigurationRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda void döner.
     */
    public function saveConfigurationAsync(
        \Odeal\Sdk\Models\ConfigurationRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/configuration';

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
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            null
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
        $path = '/configuration';

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
            \Odeal\Sdk\Models\ConfigurationResponse::class
        );
    }

    /**
     * [ASYNC] Konfigürasyon Getir
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\ConfigurationResponse ya da null (boş yanıt) döner.
     */
    public function getConfigurationAsync(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/configuration';

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
            \Odeal\Sdk\Models\ConfigurationResponse::class
        );
    }
}
