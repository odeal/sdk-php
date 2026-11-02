<?php

declare(strict_types=1);

namespace Odeal\Sdk\Resources;

use Odeal\Sdk\BaseResource;
use Odeal\Sdk\OdealConfig;
use Odeal\Sdk\Models;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Unit API islemleri.
 */
class UnitResource extends BaseResource
{
    public function __construct(GuzzleClient $httpClient, OdealConfig $config)
    {
        parent::__construct($httpClient, $config);
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
        $path = '/unit';

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
            'list',
            \Odeal\Sdk\Models\Unit::class
        );
    }

    /**
     * [ASYNC] Birimleri Listele
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\Unit[] döner.
     */
    public function listUnitsAsync(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/unit';

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
            'list',
            \Odeal\Sdk\Models\Unit::class
        );
    }
}
