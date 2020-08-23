<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 02:36:26
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsResponse;

/**
 * Ответ на запрос OrderReturn.
 *
 * @property-read OrderReturnRequest $request
 */
class OrderReturnResponse extends MonoPartsResponse
{
    /** @var string в случае успеха статус "OK" */
    public $status;
}

