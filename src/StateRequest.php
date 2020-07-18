<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 18.07.20 22:51:28
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\validate\ValidateException;
use yii\base\Exception;
use yii\httpclient\Client;

/**
 * Запрос состояния заявки на покупку частями.
 *
 * @api https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/getOrderStateUsingPOST
 */
class StateRequest extends AbstractRequest
{
    /** @var string номер заявки полученный в PaymentRequest */
    public $orderId;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['orderId', 'trim'],
            ['orderId', 'required']
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
            'order_id' => (string)$this->orderId
        ];
    }

    /**
     * Отправка запроса.
     *
     * @return StateResponse
     * @throws Exception
     * @throws ValidateException
     * @throws \yii\httpclient\Exception
     */
    public function send()
    {
        $request = $this->module->httpClient->post('/api/order/state', $this->toJson());
        $request = $this->signRequest($request);

        $response = $request->send();
        $response->format = Client::FORMAT_JSON;

        if (! $response->isOk) {
            throw new Exception('Ошибка запроса: ' . $response->data['message'] ?? $response->toString());
        }

        return new StateResponse($response->data);
    }
}
