<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Sepet listesindeki bir sepetin özeti.
 */
class BasketSummary implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
    ];
    /**
     * @var int
     */
    public ?int $id = null;
    /**
     * @var string
     */
    public ?string $referenceCode = null;
    /**
     * @var string
     */
    public ?string $basketAlias = null;
    /**
     * @var string
     */
    public ?string $basketType = null;
    /**
     * @var string
     */
    public ?string $status = null;
    /**
     * @var float
     */
    public ?float $netPrice = null;
    /**
     * @var float
     */
    public ?float $grossPrice = null;
    /**
     * @var float
     */
    public ?float $vatPrice = null;
    /**
     * @var float
     */
    public ?float $sctPrice = null;
    /**
     * @var float
     */
    public ?float $accPrice = null;
    /**
     * @var int
     */
    public ?int $customerId = null;
    /**
     * @var int
     */
    public ?int $deviceId = null;
    /**
     * @var string
     */
    public ?string $createdDate = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'id' => $this->id,
            'referenceCode' => $this->referenceCode,
            'basketAlias' => $this->basketAlias,
            'basketType' => $this->basketType,
            'status' => $this->status,
            'netPrice' => $this->netPrice,
            'grossPrice' => $this->grossPrice,
            'vatPrice' => $this->vatPrice,
            'sctPrice' => $this->sctPrice,
            'accPrice' => $this->accPrice,
            'customerId' => $this->customerId,
            'deviceId' => $this->deviceId,
            'createdDate' => $this->createdDate,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}