<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class Product implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
        'unitCode' => 'Odeal\Sdk\Enums\ProductUnitCode',
    ];

    public const VALIDATION_RULES = [
        'unitCode' => ['required' => true,
        ],
        'name' => ['required' => true,'pattern' => '/^.{1,255}$/', 'message' => 'Ürün adı 1-255 karakter arasında olmalıdır.'
        ],
        'referenceCode' => ['required' => true,'pattern' => '/^.{1,50}$/', 'message' => 'Ürün kodu 1-50 karakter arasında olmalıdır.'
        ],
        'price' => ['required' => true,
        ],
    ];
    /**
     * @var \Odeal\Sdk\Enums\ProductUnitCode
     */
    public \Odeal\Sdk\Enums\ProductUnitCode $unitCode;
    /**
     * @var string
     */
    public string $name;
    /**
     * @var string
     */
    public string $referenceCode;
    /**
     * @var \Odeal\Sdk\Models\ProductPrice
     */
    public \Odeal\Sdk\Models\ProductPrice $price;
    /**
     * @var \Odeal\Sdk\Models\Exemption
     */
    public ?\Odeal\Sdk\Models\Exemption $exemption = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'unitCode' => $this->unitCode,
            'name' => $this->name,
            'referenceCode' => $this->referenceCode,
            'price' => $this->price,
            'exemption' => $this->exemption,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}