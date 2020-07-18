<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 19.07.20 02:25:16
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\validate\ValidateException;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * Валидация клиента (версия 2).
 *
 * @api https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/validateClientUsingPOST
 */
class ValidateClientRequest extends AbstractRequest
{
    /** @var string телефон клиента */
    public $phone;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['phone', 'trim'],
            ['phone', 'required'],
            ['phone', PhoneValidator::class]
        ];
    }

    /**
     * Данные JSON.
     *
     * @return string[]
     * @throws ValidateException
     */
    protected function toJson()
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        return [
            'phone' => (string)$this->phone
        ];
    }

    /**
     * Отправка запроса.
     *
     * @return bool true, если клиент найден в базе банка
     * @throws Exception
     * @throws ValidateException
     * @throws \yii\httpclient\Exception
     */
    public function send()
    {
        $request = $this->module->httpClient->post('/api/v2/client/validate', $this->toJson());
        $request = $this->signRequest($request);

        $response = $request->send();
        $response->format = Client::FORMAT_JSON;

        if (! $response->isOk) {
            throw new Exception('Ошибка запроса: ' . $response->data['message'] ?? $response->toString());
        }

        if (! isset($response->data['found'])) {
            throw new Exception('Неизвестный формат ответа: ' . $response->toString());
        }

        return $response->data['found'] === true || $response->data['found'] === 'true';
    }
}
