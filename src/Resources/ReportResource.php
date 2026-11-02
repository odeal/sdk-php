<?php

declare(strict_types=1);

namespace Odeal\Sdk\Resources;

use Odeal\Sdk\BaseResource;
use Odeal\Sdk\OdealConfig;
use Odeal\Sdk\Models;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Report API islemleri.
 */
class ReportResource extends BaseResource
{
    public function __construct(GuzzleClient $httpClient, OdealConfig $config)
    {
        parent::__construct($httpClient, $config);
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
        $path = '/report/transactions';

        $params = [
            'beginDate' => $beginDate,
            'endDate' => $endDate,
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'externalDeviceKey' => $externalDeviceKey,
            'basketReferenceCode' => $basketReferenceCode,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'externalDeviceKey' => ['configProp' => 'externalDeviceKey'],
        ]);

        $queryParams = [];
        if ($params['beginDate'] !== null) {
            $queryParams['beginDate'] = $params['beginDate'];
        }
        if ($params['endDate'] !== null) {
            $queryParams['endDate'] = $params['endDate'];
        }
        if ($params['externalDeviceKey'] !== null) {
            $queryParams['externalDeviceKey'] = $params['externalDeviceKey'];
        }
        if ($params['basketReferenceCode'] !== null) {
            $queryParams['basketReferenceCode'] = $params['basketReferenceCode'];
        }

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
            \Odeal\Sdk\Models\TransactionReport::class
        );
    }

    /**
     * [ASYNC] İşlem Raporu
     *
     * @param string $beginDate 
     * @param string $endDate 
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $externalDeviceKey 
     * @param string $basketReferenceCode 
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\TransactionReport[] döner.
     */
    public function getTransactionReportAsync(
        string $beginDate,
        string $endDate,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $externalDeviceKey = null,
        ?string $basketReferenceCode = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/report/transactions';

        $params = [
            'beginDate' => $beginDate,
            'endDate' => $endDate,
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'externalDeviceKey' => $externalDeviceKey,
            'basketReferenceCode' => $basketReferenceCode,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'externalDeviceKey' => ['configProp' => 'externalDeviceKey'],
        ]);

        $queryParams = [];
        if ($params['beginDate'] !== null) {
            $queryParams['beginDate'] = $params['beginDate'];
        }
        if ($params['endDate'] !== null) {
            $queryParams['endDate'] = $params['endDate'];
        }
        if ($params['externalDeviceKey'] !== null) {
            $queryParams['externalDeviceKey'] = $params['externalDeviceKey'];
        }
        if ($params['basketReferenceCode'] !== null) {
            $queryParams['basketReferenceCode'] = $params['basketReferenceCode'];
        }

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
            \Odeal\Sdk\Models\TransactionReport::class
        );
    }
}
