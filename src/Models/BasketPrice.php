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
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'grossPrice' => $this->grossPrice,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}