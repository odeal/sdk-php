<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Tek bir alan icin validasyon hatasi.
 */
class ValidationError implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
    ];
    /**
     * @var string
     */
    public ?string $field = null;
    /**
     * @var string
     */
    public ?string $message = null;
    /**
     * @var string
     */
    public ?string $rejectedValue = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'field' => $this->field,
            'message' => $this->message,
            'rejectedValue' => $this->rejectedValue,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}