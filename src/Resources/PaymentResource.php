<?php

declare(strict_types=1);

namespace Odeal\Sdk\Resources;

use Odeal\Sdk\BaseResource;
use Odeal\Sdk\OdealConfig;
use Odeal\Sdk\Models;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Payment API islemleri.
 */
class PaymentResource extends BaseResource
{
    public function __construct(GuzzleClient $httpClient, OdealConfig $config)
    {
        parent::__construct($httpClient, $config);
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
        $path = '/payment/cancel';

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
            'Put',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            'object',
            \Odeal\Sdk\Models\CancelPaymentResponse::class
        );
    }

    /**
     * [ASYNC] Ödeme İptali
     *
     * @param \Odeal\Sdk\Models\CancelPaymentRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\CancelPaymentResponse ya da null (boş yanıt) döner.
     */
    public function cancelPaymentAsync(
        \Odeal\Sdk\Models\CancelPaymentRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/payment/cancel';

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
            'Put',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl'],
            'object',
            \Odeal\Sdk\Models\CancelPaymentResponse::class
        );
    }
}
