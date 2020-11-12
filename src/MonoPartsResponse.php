<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:08:22
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\json\JsonEntity;

/**
 * Ответ на запрос.
 *
 * @property-write array $data
 */
abstract class MonoPartsResponse extends JsonEntity
{
    /** @var ?string сообщение в случае ошибки */
    public $message;
}
