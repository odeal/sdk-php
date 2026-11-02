<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class TransactionReport implements JsonSerializable
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
    public ?string $basketReferenceCode = null;
    /**
     * @var string
     */
    public ?string $paymentId = null;
    /**
     * @var string
     */
    public ?string $currentStatus = null;
    /**
     * @var string
     */
    public ?string $amount = null;
    /**
     * @var string
     */
    public ?string $invoiceNumber = null;
    /**
     * @var string
     */
    public ?string $invoicePdfUrl = null;
    /**
     * @var string
     */
    public ?string $basketStatus = null;
    /**
     * @var int
     */
    public ?int $invoiceGibStatusCode = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'basketReferenceCode' => $this->basketReferenceCode,
            'paymentId' => $this->paymentId,
            'currentStatus' => $this->currentStatus,
            'amount' => $this->amount,
            'invoiceNumber' => $this->invoiceNumber,
            'invoicePdfUrl' => $this->invoicePdfUrl,
            'basketStatus' => $this->basketStatus,
            'invoiceGibStatusCode' => $this->invoiceGibStatusCode,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}