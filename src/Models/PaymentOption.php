<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class PaymentOption implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
        'type' => 'Odeal\Sdk\Enums\PaymentOptionType',
    ];

    public const VALIDATION_RULES = [
        'amount' => ['required' => true,
        ],
        'type' => ['required' => true,
        ],
    ];
    /**
     * @var float
     */
    public float $amount;
    /**
     * @var \Odeal\Sdk\Enums\PaymentOptionType
     */
    public \Odeal\Sdk\Enums\PaymentOptionType $type;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'amount' => $this->amount,
            'type' => $this->type,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}