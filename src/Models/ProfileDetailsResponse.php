<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class ProfileDetailsResponse implements JsonSerializable
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
     * @var int
     */
    public ?int $organisationId = null;
    /**
     * @var string
     */
    public ?string $secretKey = null;
    /**
     * @var string
     */
    public ?string $apiKey = null;
    /**
     * @var string
     */
    public ?string $ipList = null;
    /**
     * @var string
     */
    public ?string $profileType = null;
    /**
     * @var int
     */
    public ?int $terminalId = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'id' => $this->id,
            'organisationId' => $this->organisationId,
            'secretKey' => $this->secretKey,
            'apiKey' => $this->apiKey,
            'ipList' => $this->ipList,
            'profileType' => $this->profileType,
            'terminalId' => $this->terminalId,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}