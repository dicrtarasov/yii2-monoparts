<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:47:41
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\monoparts\request\OrderCreateRequest;
use dicr\monoparts\request\OrderStateRequest;
use dicr\monoparts\request\ValidateClientRequest;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\web\Application;
use yii\web\JsonParser;

use function array_merge;
use function base64_encode;
use function hash_hmac;
use function is_callable;

/**
 * Модуль оплаты частями от Monobank v1.0.0.
 *
 * Для теста:
 * - успешное подтверждение заявки - передать номер телефона клиента, который заканчивается на 1 - через 5 секунд
 * будет брошен callback об успешном утверждении заявки.
 * - заявка, которая ожидает подтверждения от клиента - передать номер телефона клиента, который заканчивается на 2.
 * - ошибка подтверждения заявки по причине недостаточного лимита у клиента - передать номер телефона клиента,
 * который заканчивается на 3. Через 5 секунд будет брошен callback об отклонении заявки.
 * - заявка подтверждена клиентом и ожидает подтверждения от магазина (только для магазина с двухэтапным
 * подтверждением) - передать номер телефона клиента, который заканчивается на 4.
 *
 * @api https://u2-demo-ext.mono.st4g3.com/docs/index.html
 * @api https://u2-demo.ftband.com/docs/index.html
 *
 * @property-read Client $httpClient
 */
class MonoPartsModule extends Module implements MonoParts
{
    /** @var string адрес API */
    public $url = self::API_URL;

    /** @var string идентификатор магазина */
    public $storeId;

    /** @var string ключ магазина */
    public $secretKey;

    /** @var array конфиг HTTP-клиента */
    public $httpClientConfig = [];

    /** @var array конфиг по-умолчанию для CreateOrderRequest */
    public $createRequestConfig = [];

    /** @var ?callable function(OrderStateResponse $response) обработчик оплаты покупки */
    public $handler;

    /** @inheritDoc */
    public $controllerNamespace = __NAMESPACE__;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init() : void
    {
        parent::init();

        if (empty($this->url)) {
            throw new InvalidConfigException('url');
        }

        if (empty($this->storeId)) {
            throw new InvalidConfigException('storeId');
        }

        if (empty($this->secretKey)) {
            throw new InvalidConfigException('secretKey');
        }

        if (! empty($this->paymentHandler) && ! is_callable($this->paymentHandler)) {
            throw new InvalidConfigException('paymentHandler');
        }

        if (Yii::$app instanceof Application) {
            // парсер JSON-запросов от банка
            Yii::$app->request->parsers['application/json'] = JsonParser::class;
        }
    }

    /**
     * Генерирует сигнатуру.
     *
     * @param string $content
     * @return string
     */
    public function signature(string $content) : string
    {
        return base64_encode(hash_hmac('sha256', $content, $this->secretKey, true));
    }

    /** @var Client */
    private $_httpClient;

    /**
     * Клиент HTTP.
     *
     * @return Client
     * @throws InvalidConfigException
     */
    public function getHttpClient() : Client
    {
        if (! isset($this->_httpClient)) {
            $this->_httpClient = Yii::createObject(array_merge([
                'class' => Client::class,
                'baseUrl' => $this->url,
            ], $this->httpClientConfig ?: []));
        }

        return $this->_httpClient;
    }

    /**
     * Создает запрос.
     *
     * @param array $config
     * @return MonoPartsRequest
     * @throws InvalidConfigException
     */
    public function request(array $config) : MonoPartsRequest
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config, [$this]);
    }

    /**
     * Создает запрос на оплату.
     *
     * @param array $config
     * @return OrderCreateRequest
     * @throws InvalidConfigException
     */
    public function orderCreateRequest(array $config = []) : OrderCreateRequest
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->request(array_merge([
            'class' => OrderCreateRequest::class,
            'callback' => Url::to(['/' . $this->uniqueId . '/callback'], true)
        ], $this->createRequestConfig ?: [], $config));
    }

    /**
     * Создает запрос на валидацию клиента.
     *
     * @param array $config
     * @return ValidateClientRequest
     * @throws InvalidConfigException
     */
    public function validateClientRequest(array $config = []) : ValidateClientRequest
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->request(array_merge([
            'class' => ValidateClientRequest::class
        ], $this->createRequestConfig ?: [], $config));
    }

    /**
     * Создает запрос проверки статуса заявки.
     *
     * @param array $config
     * @return OrderStateRequest
     * @throws InvalidConfigException
     */
    public function orderStateRequest(array $config = []) : OrderStateRequest
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->request(array_merge([
            'class' => OrderStateRequest::class
        ], $this->createRequestConfig ?: [], $config));
    }
}
