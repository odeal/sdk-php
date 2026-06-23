<?php

declare(strict_types=1);

namespace Odeal\Sdk\Resources;

use Odeal\Sdk\BaseResource;
use Odeal\Sdk\OdealConfig;
use Odeal\Sdk\Models;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Basket API islemleri.
 */
class BasketResource extends BaseResource
{
    public function __construct(GuzzleClient $httpClient, OdealConfig $config)
    {
        parent::__construct($httpClient, $config);
    }

    /**
     * Standart ürün satışı. Müşteri Bireysel veya Kurumsal olabilir. 'items' alanı zorunludur.
     *
     * @param \Odeal\Sdk\Models\BasketRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse
     */
    public function createSimpleBasket(
        \Odeal\Sdk\Models\BasketRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): \Odeal\Sdk\Models\BasketResponse {
        $path = '/basket';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        $response = $this->send(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
        return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
    }

    /**
     * [ASYNC] Standart ürün satışı. Müşteri Bireysel veya Kurumsal olabilir. 'items' alanı zorunludur.
     *
     * @param \Odeal\Sdk\Models\BasketRequest $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\BasketResponse döner.
     */
    public function createSimpleBasketAsync(
        \Odeal\Sdk\Models\BasketRequest $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/basket';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->sendAsync(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        )->then(function (string $response) {
            return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
        });
    }

    /**
     * Avans tahsilatı. Müşteri Bireysel veya Kurumsal olabilir. 'items' gönderilmez. `basketType` ADVANCE olmalıdır.
     *
     * @param \Odeal\Sdk\Models\BasketRequestAdvance $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse
     */
    public function createAdvanceBasket(
        \Odeal\Sdk\Models\BasketRequestAdvance $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): \Odeal\Sdk\Models\BasketResponse {
        $path = '/basket/advance';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        $response = $this->send(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
        return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
    }

    /**
     * [ASYNC] Avans tahsilatı. Müşteri Bireysel veya Kurumsal olabilir. 'items' gönderilmez. `basketType` ADVANCE olmalıdır.
     *
     * @param \Odeal\Sdk\Models\BasketRequestAdvance $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\BasketResponse döner.
     */
    public function createAdvanceBasketAsync(
        \Odeal\Sdk\Models\BasketRequestAdvance $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/basket/advance';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->sendAsync(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        )->then(function (string $response) {
            return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
        });
    }

    /**
     * Cari hesap tahsilatı. Müşteri Kurumsal olmalıdır. `basketType` CURRENT_ACCOUNT olmalıdır.
     *
     * @param \Odeal\Sdk\Models\BasketRequestCurrentAccount $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse
     */
    public function createCurrentAccountBasket(
        \Odeal\Sdk\Models\BasketRequestCurrentAccount $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): \Odeal\Sdk\Models\BasketResponse {
        $path = '/basket/current-account';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        $response = $this->send(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
        return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
    }

    /**
     * [ASYNC] Cari hesap tahsilatı. Müşteri Kurumsal olmalıdır. `basketType` CURRENT_ACCOUNT olmalıdır.
     *
     * @param \Odeal\Sdk\Models\BasketRequestCurrentAccount $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\BasketResponse döner.
     */
    public function createCurrentAccountBasketAsync(
        \Odeal\Sdk\Models\BasketRequestCurrentAccount $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/basket/current-account';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->sendAsync(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        )->then(function (string $response) {
            return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
        });
    }

    /**
     * Yemek kartı işlemleri. `receiptInfo` ve içindeki `foodCardBrandId` zorunludur.
     *
     * @param \Odeal\Sdk\Models\BasketRequestFoodCard $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketResponse
     */
    public function createFoodCardBasket(
        \Odeal\Sdk\Models\BasketRequestFoodCard $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): \Odeal\Sdk\Models\BasketResponse {
        $path = '/basket/foodCard';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        $response = $this->send(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
        return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
    }

    /**
     * [ASYNC] Yemek kartı işlemleri. `receiptInfo` ve içindeki `foodCardBrandId` zorunludur.
     *
     * @param \Odeal\Sdk\Models\BasketRequestFoodCard $request
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\BasketResponse döner.
     */
    public function createFoodCardBasketAsync(
        \Odeal\Sdk\Models\BasketRequestFoodCard $request,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/basket/foodCard';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->sendAsync(
            'Post',
            $path,
            $request,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        )->then(function (string $response) {
            return $this->deserialize($response, \Odeal\Sdk\Models\BasketResponse::class);
        });
    }

    /**
     * Sepet Listele
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $externalDeviceKey 
     * @param string $baseUrl API sunucusunun adresi.
     * @return \Odeal\Sdk\Models\BasketListResponse
     */
    public function listBaskets(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $externalDeviceKey = null,
        ?string $baseUrl = null
    ): \Odeal\Sdk\Models\BasketListResponse {
        $path = '/basket/list';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'externalDeviceKey' => $externalDeviceKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'externalDeviceKey' => ['configProp' => 'externalDeviceKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }
        if ($params['externalDeviceKey'] !== null) {
            $headerParams['externalDeviceKey'] = $params['externalDeviceKey'];
        }

        $response = $this->send(
            'Get',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
        return $this->deserialize($response, \Odeal\Sdk\Models\BasketListResponse::class);
    }

    /**
     * [ASYNC] Sepet Listele
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $externalDeviceKey 
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda \Odeal\Sdk\Models\BasketListResponse döner.
     */
    public function listBasketsAsync(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $externalDeviceKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/basket/list';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'externalDeviceKey' => $externalDeviceKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'externalDeviceKey' => ['configProp' => 'externalDeviceKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }
        if ($params['externalDeviceKey'] !== null) {
            $headerParams['externalDeviceKey'] = $params['externalDeviceKey'];
        }

        return $this->sendAsync(
            'Get',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        )->then(function (string $response) {
            return $this->deserialize($response, \Odeal\Sdk\Models\BasketListResponse::class);
        });
    }

    /**
     * Sepet Sil
     *
     * @param string $referenceCode Silinecek sepetin referans kodu
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return void
     */
    public function deleteBasket(
        string $referenceCode,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): void {
        $path = '/basket/delete';

        $params = [
            'referenceCode' => $referenceCode,
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }
        if ($params['referenceCode'] !== null) {
            $headerParams['referenceCode'] = $params['referenceCode'];
        }

        $this->send(
            'Delete',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
    }

    /**
     * [ASYNC] Sepet Sil
     *
     * @param string $referenceCode Silinecek sepetin referans kodu
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda void döner.
     */
    public function deleteBasketAsync(
        string $referenceCode,
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/basket/delete';

        $params = [
            'referenceCode' => $referenceCode,
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }
        if ($params['referenceCode'] !== null) {
            $headerParams['referenceCode'] = $params['referenceCode'];
        }

        return $this->sendAsync(
            'Delete',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
    }

    /**
     * Tüm Sepetleri Sil
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return void
     */
    public function deleteAllBaskets(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): void {
        $path = '/basket/delete-all-baskets';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        $this->send(
            'Delete',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
    }

    /**
     * [ASYNC] Tüm Sepetleri Sil
     *
     * @param string $secretKey Size özel olarak verilmiş gizli anahtar.
     * @param string $merchantKey Size özel olarak verilmiş iş yeri anahtarı.
     * @param string $baseUrl API sunucusunun adresi.
     * @return PromiseInterface Promise resolve olduğunda void döner.
     */
    public function deleteAllBasketsAsync(
        ?string $secretKey = null,
        ?string $merchantKey = null,
        ?string $baseUrl = null
    ): PromiseInterface {
        $path = '/basket/delete-all-baskets';

        $params = [
            'secretKey' => $secretKey,
            'merchantKey' => $merchantKey,
            'baseUrl' => $baseUrl,
        ];

        $this->applyConfigDefaults($params, [
            'secretKey' => ['configProp' => 'secretKey'],
            'merchantKey' => ['configProp' => 'merchantKey'],
            'baseUrl' => ['configProp' => 'baseUrl'],
        ]);

        $queryParams = [];

        $headerParams = [];
        if ($params['secretKey'] !== null) {
            $headerParams['X-ODEAL-SECRET-KEY'] = $params['secretKey'];
        }
        if ($params['merchantKey'] !== null) {
            $headerParams['X-ODEAL-MERCHANT-KEY'] = $params['merchantKey'];
        }

        return $this->sendAsync(
            'Delete',
            $path,
            null,
            $queryParams,
            $headerParams,
            $params['baseUrl']
        );
    }
}
