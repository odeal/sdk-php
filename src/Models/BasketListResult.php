<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Sepet listeleme sonucu.
 */
class BasketListResult implements JsonSerializable
{
    public const ARRAY_TYPES = [
        'baskets' => 'Odeal\Sdk\Models\BasketSummary',
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
    ];
    /**
     * @var \Odeal\Sdk\Models\BasketSummary[]
     */
    public ?array $baskets = [];
    /**
     * @var int
     */
    public ?int $totalPages = null;
    /**
     * @var int
     */
    public ?int $totalElements = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'baskets' => $this->baskets,
            'totalPages' => $this->totalPages,
            'totalElements' => $this->totalElements,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}