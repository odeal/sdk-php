<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class Exemption implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
        'code' => ['pattern' => '/^.{1,}$/', 'message' => 'Code formatı geçersiz.'
        ],
    ];
    /**
     * @var string
     */
    public ?string $code = null;
    /**
     * @var string
     */
    public ?string $description = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'code' => $this->code,
            'description' => $this->description,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}