<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:50:46
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoParts;
use dicr\monoparts\MonoPartsResponse;

/**
 * Ответ на запрос OrderState
 *
 * @link https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/checkStateUsingPOST
 */
class OrderStateResponse extends MonoPartsResponse implements MonoParts
{
    /** @var string */
    public $orderId;

    /** @var string результат выполнения оплаты */
    public $state;

    /** @var string уточнение причины состояния */
    public $subState;

    /**
     * @inheritDoc
     */
    public function setData(array $data) : void
    {
        $this->orderId = isset($data['order_id']) ? (string)$data['order_id'] : null;
        $this->state = isset($data['state']) ? (string)$data['state'] : null;
        $this->subState = isset($data['order_sub_state']) ? (string)$data['order_sub_state'] : null;
    }
}
