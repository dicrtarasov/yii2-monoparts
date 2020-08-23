<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 02:28:03
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsResponse;

/**
 * Ответ на запрос OrderReject.
 *
 * @property-read OrderRejectRequest $request
 */
class OrderRejectResponse extends MonoPartsResponse
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
    public function setData(array $data)
    {
        $this->orderId = isset($data['order_id']) ? (string)$data['order_id'] : null;
        $this->state = isset($data['state']) ? (string)$data['state'] : null;
        $this->subState = isset($data['order_sub_state']) ? (string)$data['order_sub_state'] : null;
    }
}

