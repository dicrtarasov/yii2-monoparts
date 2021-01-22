<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:08:14
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\json\JsonEntity;
use dicr\validate\ValidateException;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;
use yii\httpclient\Client;

use function array_filter;

/**
 * Базовый класс запросов.
 */
abstract class MonoPartsRequest extends JsonEntity implements MonoParts
{
    /** @var MonoPartsModule */
    protected $_module;

    /**
     * PaymentRequest constructor.
     *
     * @param MonoPartsModule $module
     * @param array $config
     */
    public function __construct(MonoPartsModule $module, array $config = [])
    {
        $this->_module = $module;

        parent::__construct($config);
    }

    /**
     * Адрес запроса.
     *
     * @return string
     */
    abstract protected function url(): string;

    /**
     * Метод HTTP-запроса.
     *
     * @return string
     */
    protected function method(): string
    {
        return 'post';
    }

    /**
     * Отправка запроса.
     *
     * @return array данные ответа (переопределяется в реализации)
     * @throws Exception
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function send()
    {
        // валидация
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        // JSON
        $json = Json::encode(array_filter(
                $this->json,
                static fn($val): bool => $val !== null && $val !== '' && $val !== [])
        );

        // запрос
        $request = $this->_module->httpClient->createRequest()
            ->setMethod($this->method())
            ->setUrl($this->url())
            ->setContent($json)
            ->setHeaders([
                'Content-Type' => 'application/json;charset=UTF-8',
                'Accept' => 'application/json',
                'Accept-Encoding' => 'UTF-8',
                'store-id' => $this->_module->storeId,
                'signature' => $this->_module->signature($json)
            ]);

        Yii::debug('Запрос: ' . $request->toString(), __METHOD__);
        $response = $request->send();
        Yii::debug('Ответ: ' . $response->toString(), __METHOD__);

        $response->format = Client::FORMAT_JSON;
        if (! $response->isOk) {
            throw new Exception('HTTP-error: ' . $response->data['message'] ?? $response->statusCode);
        }

        return $response->data;
    }
}
