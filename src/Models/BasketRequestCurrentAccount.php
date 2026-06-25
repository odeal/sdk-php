<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class BasketRequestCurrentAccount implements JsonSerializable
{
    public const ARRAY_TYPES = [
        'paymentOptions' => 'Odeal\Sdk\Models\PaymentOption',
    ];

    public const ENUM_TYPES = [
        'basketType' => 'Odeal\Sdk\Enums\BasketType',
    ];

    public const VALIDATION_RULES = [
        'referenceCode' => ['required' => true,'pattern' => '/^.{1,50}$/', 'message' => 'Referans kodu 1-50 karakter arasında olmalıdır.'
        ],
        'externalDeviceKey' => ['pattern' => '/^.{1,}$/', 'message' => 'Cihaz kodu boş olamaz.'
        ],
        'receiptInfo' => ['required' => true,
        ],
        'customer' => ['required' => true,
        ],
        'price' => ['required' => true,
        ],
        'paymentOptions' => ['required' => true,
        ],
    ];
    /**
     * @var string
     */
    public string $referenceCode;
    /**
     * @var string
     */
    #[OdealClientDefault]
    public ?string $externalDeviceKey = null;
    /**
     * @var \Odeal\Sdk\Enums\BasketType
     */
    public \Odeal\Sdk\Enums\BasketType $basketType = \Odeal\Sdk\Enums\BasketType::CURRENTACCOUNT;
    /**
     * @var \Odeal\Sdk\Models\ReceiptInfo
     */
    public \Odeal\Sdk\Models\ReceiptInfo $receiptInfo;
    /**
     * @var \Odeal\Sdk\Models\Customer
     */
    public \Odeal\Sdk\Models\Customer $customer;
    /**
     * @var \Odeal\Sdk\Models\BasketPrice
     */
    public \Odeal\Sdk\Models\BasketPrice $price;
    /**
     * @var \Odeal\Sdk\Models\PaymentOption[]
     */
    public array $paymentOptions = [];

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'referenceCode' => $this->referenceCode,
            'externalDeviceKey' => $this->externalDeviceKey,
            'basketType' => $this->basketType,
            'receiptInfo' => $this->receiptInfo,
            'customer' => $this->customer,
            'price' => $this->price,
            'paymentOptions' => $this->paymentOptions,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}