<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:13:03
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsRequest;
use yii\base\Exception;

/**
 * Запрос состояния заявки на покупку частями.
 *
 * @api https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/getOrderStateUsingPOST
 */
class OrderStateRequest extends MonoPartsRequest
{
    /** @var string номер заявки покупки частями, полученный в PaymentRequest */
    public $orderId;

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['orderId', 'trim'],
            ['orderId', 'required'],
            ['orderId', 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function url() : string
    {
        return 'order/state';
    }

    /**
     * Отправка запроса.
     *
     * @return OrderStateResponse
     * @throws Exception
     */
    public function send() : OrderStateResponse
    {
        return new OrderStateResponse([
            'json' => parent::send()
        ]);
    }
}
