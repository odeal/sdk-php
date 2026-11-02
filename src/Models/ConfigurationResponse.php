<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Konfigürasyon bilgisi.
 */
class ConfigurationResponse implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
    ];
    /**
     * @var string
     */
    public ?string $ecommerceUrl = null;
    /**
     * @var string
     */
    public ?string $basketUrl = null;
    /**
     * @var string
     */
    public ?string $paymentSucceededUrl = null;
    /**
     * @var string
     */
    public ?string $paymentCancelledUrl = null;
    /**
     * @var string
     */
    public ?string $paymentFailedUrl = null;
    /**
     * @var string
     */
    public ?string $einvoiceCreatedUrl = null;
    /**
     * @var string
     */
    public ?string $einvoiceCancelledUrl = null;
    /**
     * @var string
     */
    public ?string $callbackPayoutUrl = null;
    /**
     * @var string
     */
    public ?string $basketCancelledUrl = null;
    /**
     * @var string
     */
    public ?string $basketProcessFailedUrl = null;
    /**
     * @var string
     */
    public ?string $einvoiceIntegrator = null;
    /**
     * @var string
     */
    public ?string $basketType = null;
    /**
     * @var string
     */
    public ?string $odealRequestKey = null;
    /**
     * @var string
     */
    public ?string $customerGetUrl = null;
    /**
     * @var string
     */
    public ?string $customerPostUrl = null;
    /**
     * @var string
     */
    public ?string $customerPutUrl = null;
    /**
     * @var string
     */
    public ?string $intentUrl = null;
    /**
     * @var string
     */
    public ?string $appName = null;
    /**
     * @var bool
     */
    public ?bool $closeAfterPayment = null;
    /**
     * @var string
     */
    public ?string $authorizationType = null;
    /**
     * @var string
     */
    public ?string $basicAuthUsername = null;
    /**
     * @var string
     */
    public ?string $basicAuthPassword = null;
    /**
     * @var string
     */
    public ?string $terminalId = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'ecommerceUrl' => $this->ecommerceUrl,
            'basketUrl' => $this->basketUrl,
            'paymentSucceededUrl' => $this->paymentSucceededUrl,
            'paymentCancelledUrl' => $this->paymentCancelledUrl,
            'paymentFailedUrl' => $this->paymentFailedUrl,
            'einvoiceCreatedUrl' => $this->einvoiceCreatedUrl,
            'einvoiceCancelledUrl' => $this->einvoiceCancelledUrl,
            'callbackPayoutUrl' => $this->callbackPayoutUrl,
            'basketCancelledUrl' => $this->basketCancelledUrl,
            'basketProcessFailedUrl' => $this->basketProcessFailedUrl,
            'einvoiceIntegrator' => $this->einvoiceIntegrator,
            'basketType' => $this->basketType,
            'odealRequestKey' => $this->odealRequestKey,
            'customerGetUrl' => $this->customerGetUrl,
            'customerPostUrl' => $this->customerPostUrl,
            'customerPutUrl' => $this->customerPutUrl,
            'intentUrl' => $this->intentUrl,
            'appName' => $this->appName,
            'closeAfterPayment' => $this->closeAfterPayment,
            'authorizationType' => $this->authorizationType,
            'basicAuthUsername' => $this->basicAuthUsername,
            'basicAuthPassword' => $this->basicAuthPassword,
            'terminalId' => $this->terminalId,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}