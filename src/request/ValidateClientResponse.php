<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 02:38:25
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsResponse;

/**
 * Ответ на запрос ValidateClient.
 *
 * @property-read ValidateClientRequest $request
 */
class ValidateClientResponse extends MonoPartsResponse
{
    /** @var bool клиент найден */
    public $found;
}
