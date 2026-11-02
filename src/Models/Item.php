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
        'allowances' => 'Odeal\Sdk\Models\Allowance',
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
     * @var \Odeal\Sdk\Models\Allowance[]
     */
    public ?array $allowances = [];

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'quantity' => $this->quantity,
            'product' => $this->product,
            'allowances' => $this->allowances,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}