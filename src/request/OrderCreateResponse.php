<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:49:59
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsResponse;

/**
 * Ответ на запрос OrderCreate.
 *
 * @property-read OrderCreateRequest $request
 */
class OrderCreateResponse extends MonoPartsResponse
{
    /** @var string */
    public $orderId;

    /**
     * @inheritDoc
     */
    public function setData(array $data) : void
    {
        $this->orderId = isset($data['order_id']) ? (string)$data['order_id'] : null;
    }
}

