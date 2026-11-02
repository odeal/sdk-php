<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Fiş detayları. Tüm sepet tipleri için ortak yapıdır.
 */
class ReceiptInfo implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
        'foodCardBrandId' => 'Odeal\Sdk\Enums\ReceiptInfoFoodCardBrandId',
    ];

    public const VALIDATION_RULES = [
        'receiptNumber' => ['pattern' => '/^.{1,50}$/', 'message' => 'ReceiptNumber formatı geçersiz.'
        ],
        'receiptDate' => ['pattern' => '/^\d{4}-\d{2}-\d{2}$/', 'message' => 'Fiş tarihi YYYY-MM-DD formatında olmalıdır.'
        ],
        'siparisNo' => ['pattern' => '/^.{0,50}$/', 'message' => 'SiparisNo formatı geçersiz.'
        ],
        'garson' => ['pattern' => '/^.{0,50}$/', 'message' => 'Garson formatı geçersiz.'
        ],
    ];
    /**
     * @var \Odeal\Sdk\Enums\ReceiptInfoFoodCardBrandId
     */
    public ?\Odeal\Sdk\Enums\ReceiptInfoFoodCardBrandId $foodCardBrandId = null;
    /**
     * @var string
     */
    public ?string $receiptNumber = null;
    /**
     * @var string
     */
    public ?string $receiptDate = null;
    /**
     * @var string
     */
    public ?string $siparisNo = null;
    /**
     * @var string
     */
    public ?string $garson = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'foodCardBrandId' => $this->foodCardBrandId,
            'receiptNumber' => $this->receiptNumber,
            'receiptDate' => $this->receiptDate,
            'SiparisNo' => $this->siparisNo,
            'Garson' => $this->garson,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}