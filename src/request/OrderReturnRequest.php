<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:13:01
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsRequest;
use yii\base\Exception;

/**
 * Возврат товара по заявке (полный или частичный)
 *
 * @link https://u2-demo.ftband.com/docs/index.html#operation/returnOrderUsingPOST
 */
class OrderReturnRequest extends MonoPartsRequest
{
    /** @var string номер заявки покупки частями, полученный в PaymentRequest */
    public $orderId;

    /** @var bool необходимо возврата денег на карту клиента. */
    public $returnMoney;

    /** @var string идентификатор возврата на стороне магазина */
    public $storeReturnId;

    /** @var float сумма товаров, которую необходимо вернуть */
    public $sum;

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['orderId', 'trim'],
            ['orderId', 'required'],
            ['orderId', 'string', 'max' => 100],

            ['returnMoney', 'required'],
            ['returnMoney', 'boolean'],
            ['returnMoney', 'filter', 'filter' => 'boolval'],

            ['storeReturnId', 'trim'],
            ['storeReturnId', 'required'],
            ['storeReturnId', 'string', 'max' => 2 ** 31 - 1],

            ['sum', 'required'],
            ['sum', 'number', 'min' => 0.01],
            ['sum', 'filter', 'filter' => static fn($sum) : float => round((float)$sum, 2)]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function url() : string
    {
        return 'order/return';
    }

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return array_merge(parent::attributeFields(), [
            'returnMoney' => 'return_money_to_card'
        ]);
    }

    /**
     * Отправка запроса.
     *
     * @return OrderReturnResponse
     * @throws Exception
     */
    public function send() : OrderReturnResponse
    {
        return new OrderReturnResponse([
            'json' => parent::send()
        ]);
    }
}
