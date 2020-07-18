<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 19.07.20 00:17:56
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\caching\CacheInterface;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\web\Application;
use yii\web\JsonParser;
use function base64_encode;
use function hash_hmac;
use function is_callable;

/**
 * Модуль оплаты частями от Monobank v1.0.0.
 *
 * @property-read Client $httpClient
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
 */
class MonopartsModule extends Module implements Monoparts
{
    /** @var string адрес API */
    public $url = self::API_URL;

    /** @var string идентификатор магазина */
    public $storeId;

    /** @var string ключ магазина */
    public $secretKey;

    /** @var array конфиг HTTP-клиента */
    public $httpClientConfig = [];

    /** @var array конфиг по-умолчанию для PaymentRequest */
    public $paymentRequestConfig = [];

    /** @var CacheInterface */
    public $cache = 'cache';

    /**
     * @var callable|null function(string $paymentId, string $orderId) обработчик оплаты заказа
     * string $paymentId - номер заявки на оплату
     * string $orderId - номер оплаченного заказа
     */
    public $paymentHandler;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->url = trim((string)$this->url);
        if (empty($this->url)) {
            throw new InvalidConfigException('url');
        }

        $this->storeId = trim((string)$this->storeId);
        if (empty($this->storeId)) {
            throw new InvalidConfigException('storeId');
        }

        $this->secretKey = trim((string)$this->secretKey);
        if (empty($this->secretKey)) {
            throw new InvalidConfigException('secretKey');
        }

        $this->cache = Instance::ensure($this->cache);

        if (! empty($this->paymentHandler) && ! is_callable($this->paymentHandler)) {
            throw new InvalidConfigException('paymentHandler');
        }

        if (Yii::$app instanceof Application) {
            // контроллеры модуля
            $this->controllerNamespace = __NAMESPACE__;

            // парсер JSON-запросов от банка
            Yii::$app->request->parsers['application/json'] = JsonParser::class;

            // добавляем адрес обработчика ответов банка
            if (! isset($this->paymentRequestConfig['callback'])) {
                $this->paymentRequestConfig['callback'] = ['/' . $this->uniqueId . '/callback'];
            }
        }
    }

    /**
     * Генерирует сигнатуру.
     *
     * @param string $content
     * @return string
     */
    public function signature(string $content)
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
    public function getHttpClient()
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
     * Создает запрос на оплату.
     *
     * @param array $config
     * @return PaymentRequest
     */
    public function createPaymentRequest(array $config = [])
    {
        return new PaymentRequest($this, array_merge(
            $this->paymentRequestConfig, $config
        ));
    }

    /**
     * Создает запрос на валидацию клиента.
     *
     * @param array $config
     * @return ValidateClientRequest
     */
    public function createValidateClientRequest(array $config = [])
    {
        return new ValidateClientRequest($this, $config);
    }

    /**
     * Создает запрос проверки статуса заявки.
     *
     * @param array $config
     * @return StateRequest
     */
    public function createStateRequest(array $config = [])
    {
        return new StateRequest($this, $config);
    }

    /**
     * Сохраняет соответствие номера заказа магазина номеру заявки на оплату частями.
     *
     * @param string $paymentId номер заявки на оплату частями
     * @param string $orderId номер заказа магазина
     */
    public function savePaymentOrder(string $paymentId, string $orderId)
    {
        $this->cache->set([__CLASS__, $this->storeId, $paymentId], $orderId);
    }

    /**
     * Возвращает номер заказа, соответствующий номеру заявки оплаты частями.
     *
     * @param string $paymentId номер заявки оплаты частями
     * @return string номер заказа магазина ('' - если не найдена)
     */
    public function loadPaymentOrder(string $paymentId)
    {
        return (string)$this->cache->get([__CLASS__, $this->storeId, $paymentId]);
    }

    /**
     * Удаляет соответствие номера заказа магазина номеру заявки.
     *
     * @param string $paymentId номер заявки на оплату частями.
     */
    public function deletePaymentOrder(string $paymentId)
    {
        $this->cache->delete([__CLASS__, $this->storeId, $paymentId]);
    }
}
