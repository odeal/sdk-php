<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * API iş kuralı hata yanıt modeli (BUSINESS hataları).
 */
class ErrorResponse implements JsonSerializable
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
    public ?int $code = null;
    /**
     * @var string
     */
    public ?string $exceptionType = null;
    /**
     * @var string
     */
    public ?string $message = null;
    /**
     * @var string
     */
    public ?string $userMessage = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'code' => $this->code,
            'exceptionType' => $this->exceptionType,
            'message' => $this->message,
            'userMessage' => $this->userMessage,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}