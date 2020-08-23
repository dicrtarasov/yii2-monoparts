<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 02:44:28
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use yii\base\Model;

/**
 * Ответ на запрос.
 *
 * @property-read ?MonoPartsRequest $request
 * @property-write array $data
 */
class MonoPartsResponse extends Model
{
    /** @var ?MonoPartsRequest */
    private $_request;

    /** @var ?string сообщение в случае ошибки */
    public $message;

    /**
     * MonoPartsResponse constructor.
     *
     * @param ?MonoPartsRequest $request
     * @param array $config
     */
    public function __construct(?MonoPartsRequest $request, $config = [])
    {
        $this->_request = $request;

        parent::__construct($config);
    }

    /**
     * Запрос.
     *
     * @return ?MonoPartsRequest (переопределяется в реализации).
     */
    public function getRequest(): ?MonoPartsRequest
    {
        return $this->_request;
    }

    /**
     * Установить данные из JSON.
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->setAttributes($data, false);
    }
}
