<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:08:34
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsRequest;
use yii\base\Exception;

/**
 * Получение признака того, что заявка полностью оплачена
 *
 * @link https://u2-demo.ftband.com/docs/index.html#operation/checkInstallmentIsPaidUsingPOST
 */
class CheckPaidRequest extends MonoPartsRequest
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
        return 'order/check/paid';
    }

    /**
     * Отправка запроса.
     *
     * @return CheckPaidResponse
     * @throws Exception
     */
    public function send() : CheckPaidResponse
    {
        return new CheckPaidResponse([
            'json' => parent::send()
        ]);
    }
}
