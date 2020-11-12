<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:14:08
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoParts;
use dicr\monoparts\MonoPartsResponse;

use function array_merge;

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
    public function attributeFields() : array
    {
        return array_merge(parent::attributeFields(), [
            'subState' => 'order_sub_state'
        ]);
    }
}
