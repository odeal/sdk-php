<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * Hata detaylari.
 */
class ApiError implements JsonSerializable
{
    public const ARRAY_TYPES = [
        'validationErrors' => 'Odeal\Sdk\Models\ValidationError',
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
    public ?string $message = null;
    /**
     * @var string
     */
    public ?string $details = null;
    /**
     * @var string
     */
    public ?string $timestamp = null;
    /**
     * @var string
     */
    public ?string $path = null;
    /**
     * @var \Odeal\Sdk\Models\ValidationError[]
     */
    public ?array $validationErrors = [];

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'code' => $this->code,
            'message' => $this->message,
            'details' => $this->details,
            'timestamp' => $this->timestamp,
            'path' => $this->path,
            'validationErrors' => $this->validationErrors,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);
        return $cleanData;
    }
}