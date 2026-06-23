<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class Item implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
        'quantity' => ['required' => true,
        ],
        'product' => ['required' => true,
        ],
    ];
    /**
     * @var int
     */
    public int $quantity;
    /**
     * @var \Odeal\Sdk\Models\Product
     */
    public \Odeal\Sdk\Models\Product $product;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'quantity' => $this->quantity,
            'product' => $this->product,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}