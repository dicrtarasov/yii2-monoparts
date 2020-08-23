<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 01:57:00
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\validate\ValidateException;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Json;
use yii\httpclient\Client;

use function array_filter;

/**
 * Базовый класс запросов.
 *
 * @property-read MonoPartsModule $module модуль
 */
abstract class MonoPartsRequest extends Model implements MonoParts
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
     * Модуль.
     *
     * @return MonoPartsModule
     */
    public function getModule(): MonoPartsModule
    {
        return $this->_module;
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
     * Данные для JSON.
     *
     * @return array
     */
    abstract protected function data(): array;

    /**
     * Отправка запроса.
     *
     * @return array данные ответа (переопределяется в реализации)
     * @throws Exception
     */
    public function send()
    {
        // валидация
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        // фильтруем данные
        $data = array_filter($this->data(), static function ($val) {
            return $val !== null && $val !== '' && $val !== [];
        });

        // JSON
        $json = Json::encode($data);

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

        Yii::debug('Отправка запроса: ' . $request->toString(), __METHOD__);

        $response = $request->send();
        $response->format = Client::FORMAT_JSON;

        if (! $response->isOk) {
            throw new Exception('Ошибка запроса: ' . $response->data['message'] ?? $response->toString());
        }

        return $response->data;
    }
}
