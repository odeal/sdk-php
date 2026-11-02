<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class ProfileUpdateRequest implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
        'profileType' => 'Odeal\Sdk\Enums\ProfileType',
    ];

    public const VALIDATION_RULES = [
        'profileType' => ['required' => true,
        ],
    ];
    /**
     * @var \Odeal\Sdk\Enums\ProfileType
     */
    public \Odeal\Sdk\Enums\ProfileType $profileType;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'profileType' => $this->profileType,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}