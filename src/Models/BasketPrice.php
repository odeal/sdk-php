<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class BasketPrice implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
        'grossPrice' => ['required' => true,
        ],
    ];
    /**
     * @var float
     */
    public float $grossPrice;
    /**
     * @var float
     */
    public ?float $totalAllowance = null;
    /**
     * @var float
     */
    public ?float $paidAmount = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'grossPrice' => $this->grossPrice,
            'totalAllowance' => $this->totalAllowance,
            'paidAmount' => $this->paidAmount,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}