<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class BasketListResponse implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
    ];
    /**
     * @var \Odeal\Sdk\Models\BasketListResult
     */
    public ?\Odeal\Sdk\Models\BasketListResult $result = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'result' => $this->result,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}