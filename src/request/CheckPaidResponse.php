<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:08:58
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsResponse;

/**
 * Ответ на запрос CheckPaid.
 */
class CheckPaidResponse extends MonoPartsResponse
{
    /** @var bool заявка полностью оплачена */
    public $paid;

    /** @var bool банк может вернуть деньги на карту */
    public $canReturn;

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return array_merge(parent::attributeFields(), [
            'paid' => 'fully_paid',
            'canReturn' => 'bank_can_return_money_to_card'
        ]);
    }
}

