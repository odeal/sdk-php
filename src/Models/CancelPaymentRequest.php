<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Ödeme iptal isteği.
 */
class CancelPaymentRequest implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
        'basketReferenceCode' => ['required' => true,'pattern' => '/^.{1,50}$/', 'message' => 'Referans kodu 1-50 karakter arasında olmalıdır.'
        ],
    ];
    /**
     * @var string
     */
    public string $basketReferenceCode;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'basketReferenceCode' => $this->basketReferenceCode,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}