<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Oluşturulan sepetin sonucu.
 */
class BasketCreateResult implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
    ];

    public const VALIDATION_RULES = [
    ];
    /**
     * @var int
     */
    public ?int $id = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'id' => $this->id,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}