<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 18.07.20 22:56:51
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use yii\base\BaseObject;

/**
 * Информация о состоянии заявки. В ответе банка.
 *
 * @link https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/checkStateUsingPOST
 */
class StateResponse extends BaseObject implements Monoparts
{
    /** @var string идентификатор заявки */
    public $order_id;

    /** @var string общий статус заявки */
    public $state;

    /** @var string детальное состояние заявки */
    public $order_sub_state;

    /** @var string|null дополнительное описание */
    public $message;
}
