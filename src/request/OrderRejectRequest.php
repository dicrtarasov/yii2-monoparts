<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:10:39
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsRequest;
use yii\base\Exception;

/**
 * Отмена заявки (Товар клиенту не выдан)
 *
 * @link https://u2-demo.ftband.com/docs/index.html#operation/rejectUsingPOST
 */
class OrderRejectRequest extends MonoPartsRequest
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
        return 'order/reject';
    }

    /**
     * Отправка запроса.
     *
     * @return OrderRejectResponse
     * @throws Exception
     */
    public function send() : OrderRejectResponse
    {
        return new OrderRejectResponse([
            'json' => parent::send()
        ]);
    }
}
