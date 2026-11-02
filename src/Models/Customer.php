<?php

declare(strict_types=1);

namespace Odeal\Sdk\Models;

use JsonSerializable;
use Odeal\Sdk\OdealClientDefault;

/**
 * 
 */
class Customer implements JsonSerializable
{
    public const ARRAY_TYPES = [
    ];

    public const ENUM_TYPES = [
        'type' => 'Odeal\Sdk\Enums\CustomerType',
    ];

    public const VALIDATION_RULES = [
        'referenceCode' => ['pattern' => '/^.{0,50}$/', 'message' => 'ReferenceCode formatı geçersiz.'
        ],
        'name' => ['pattern' => '/^.{0,100}$/', 'message' => 'Name formatı geçersiz.'
        ],
        'surname' => ['pattern' => '/^.{0,100}$/', 'message' => 'Surname formatı geçersiz.'
        ],
        'identityNumber' => ['pattern' => '/^[1-9]\d{10}$/', 'message' => 'TCKN 11 haneli olmalı.'
        ],
        'title' => ['pattern' => '/^.{0,255}$/', 'message' => 'Title formatı geçersiz.'
        ],
        'taxNumber' => ['pattern' => '/^\d{10}$/', 'message' => 'VKN 10 haneli olmalı.'
        ],
        'taxOffice' => ['pattern' => '/^.{0,100}$/', 'message' => 'TaxOffice formatı geçersiz.'
        ],
        'city' => ['required' => true,'pattern' => '/^.{1,50}$/', 'message' => 'City formatı geçersiz.'
        ],
        'town' => ['required' => true,'pattern' => '/^.{1,50}$/', 'message' => 'Town formatı geçersiz.'
        ],
        'gsmNumber' => ['pattern' => '/^[1-9][0-9]{9}$/', 'message' => 'GSM No başında 0 olmadan 10 hane olmalıdır.'
        ],
        'email' => ['pattern' => '/^.{0,100}$/', 'message' => 'Email formatı geçersiz.'
        ],
        'address' => ['pattern' => '/^.{0,500}$/', 'message' => 'Address formatı geçersiz.'
        ],
    ];
    /**
     * @var string
     */
    public ?string $referenceCode = null;
    /**
     * @var \Odeal\Sdk\Enums\CustomerType
     */
    public ?\Odeal\Sdk\Enums\CustomerType $type = \Odeal\Sdk\Enums\CustomerType::INDIVIDUAL;
    /**
     * @var string
     */
    public ?string $name = null;
    /**
     * @var string
     */
    public ?string $surname = null;
    /**
     * @var string
     */
    public ?string $identityNumber = null;
    /**
     * @var string
     */
    public ?string $title = null;
    /**
     * @var string
     */
    public ?string $taxNumber = null;
    /**
     * @var string
     */
    public ?string $taxOffice = null;
    /**
     * @var string
     */
    public string $city;
    /**
     * @var string
     */
    public string $town;
    /**
     * @var string
     */
    public ?string $gsmNumber = null;
    /**
     * @var string
     */
    public ?string $email = null;
    /**
     * @var string
     */
    public ?string $address = null;

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'referenceCode' => $this->referenceCode,
            'type' => $this->type,
            'name' => $this->name,
            'surname' => $this->surname,
            'identityNumber' => $this->identityNumber,
            'title' => $this->title,
            'taxNumber' => $this->taxNumber,
            'taxOffice' => $this->taxOffice,
            'city' => $this->city,
            'town' => $this->town,
            'gsmNumber' => $this->gsmNumber,
            'email' => $this->email,
            'address' => $this->address,
        ];

        // Null değerleri temizle
        $cleanData = array_filter($data, fn($val) => $val !== null);

        // Boş model JSON'da {} olarak yazılır; boş PHP dizisi [] olurdu (sözleşme §16).
        return $cleanData === [] ? new \stdClass() : $cleanData;
    }
}