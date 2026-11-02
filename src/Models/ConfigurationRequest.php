<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class ConfigurationRequest implements JsonSerializable
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
    public ?string $eCommerceUrl = null;
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
    public ?string $paymentFailedUrl = null;
    /**
     * @var string
     */
    public ?string $paymentCancelledUrl = null;
    /**
     * @var string
     */
    public ?string $eInvoiceCreatedUrl = null;
    /**
     * @var string
     */
    public ?string $eInvoiceIntegrator = "ODEAL";
    /**
     * @var string
     */
    public ?string $odealRequestKey = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'eCommerceUrl' => $this->eCommerceUrl,
            'basketUrl' => $this->basketUrl,
            'paymentSucceededUrl' => $this->paymentSucceededUrl,
            'paymentFailedUrl' => $this->paymentFailedUrl,
            'paymentCancelledUrl' => $this->paymentCancelledUrl,
            'eInvoiceCreatedUrl' => $this->eInvoiceCreatedUrl,
            'eInvoiceIntegrator' => $this->eInvoiceIntegrator,
            'odealRequestKey' => $this->odealRequestKey,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}