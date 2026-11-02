<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Ürün satırına uygulanan iskonto. Tutarlar KDV dahildir. Önce LINE, sonra SUB iskontoları uygulanır; aynı tipte order sırası geçerlidir. İskontolu sepette sepet özetine totalAllowance ve paidAmount da gönderilir.
 */
class Allowance implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
        'subType' => 'Odeal\Sdk\Enums\AllowanceSubType',
    ];

    public const VALIDATION_RULES = [
        'isRateBased' => ['required' => true,
        ],
        'subType' => ['required' => true,
        ],
    ];
    /**
     * @var float
     */
    public ?float $price = null;
    /**
     * @var float
     */
    public ?float $rate = null;
    /**
     * @var bool
     */
    public bool $isRateBased;
    /**
     * @var \Odeal\Sdk\Enums\AllowanceSubType
     */
    public \Odeal\Sdk\Enums\AllowanceSubType $subType;
    /**
     * @var int
     */
    public ?int $order = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'price' => $this->price,
            'rate' => $this->rate,
            'isRateBased' => $this->isRateBased,
            'subType' => $this->subType,
            'order' => $this->order,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}