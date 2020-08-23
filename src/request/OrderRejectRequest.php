<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 02:27:10
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
        return 'order/reject';
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
     * @return OrderRejectResponse
     * @throws Exception
     */
    public function send(): OrderRejectResponse
    {
        return new OrderRejectResponse($this, [
            'data' => parent::send()
        ]);
    }
}
