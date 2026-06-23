<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class ProductPrice implements JsonSerializable
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
     * @var int
     */
    public ?int $vatRatio = null;
    /**
     * @var int
     */
    public ?int $sctRatio = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'grossPrice' => $this->grossPrice,
            'vatRatio' => $this->vatRatio,
            'sctRatio' => $this->sctRatio,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}