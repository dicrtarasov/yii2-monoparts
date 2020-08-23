<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 02:22:38
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsRequest;
use yii\base\Exception;

/**
 * Подтверждение выдачи товара клиенту
 *
 * @link https://u2-demo.ftband.com/docs/index.html#operation/confirmUsingPOST
 */
class OrderConfirmRequest extends MonoPartsRequest
{
    /** @var string номер заявки покупки частями, полученный в PaymentRequest */
    public $orderId;

    /**
     * @inheritDoc
     */
    public function rules()
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
    protected function url(): string
    {
        return 'order/confirm';
    }

    /**
     * @inheritDoc
     */
    protected function data(): array
    {
        return [
            'order_id' => $this->orderId
        ];
    }

    /**
     * Отправка запроса.
     *
     * @return OrderConfirmResponse
     * @throws Exception
     */
    public function send(): OrderConfirmResponse
    {
        return new OrderConfirmResponse($this, [
            'data' => parent::send()
        ]);
    }
}
