<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:09:13
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
    protected function url(): string
    {
        return 'order/confirm';
    }

    /**
     * Отправка запроса.
     *
     * @return OrderConfirmResponse
     * @throws Exception
     */
    public function send(): OrderConfirmResponse
    {
        return new OrderConfirmResponse([
            'json' => parent::send()
        ]);
    }
}
