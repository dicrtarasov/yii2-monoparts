<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:47:17
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsResponse;

/**
 * Ответ на запрос CheckPaid.
 *
 * @property-read CheckPaidRequest $request
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
    public function setData(array $data) : void
    {
        $this->paid = isset($data['fully_paid']) ? (bool)$data['fully_paid'] : null;

        $this->canReturn = isset($data['bank_can_return_money_to_card']) ?
            (bool)$data['bank_can_return_money_to_card'] : null;
    }
}

