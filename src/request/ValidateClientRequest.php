<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:47:17
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsRequest;
use dicr\monoparts\PhoneValidator;
use yii\base\Exception;

/**
 * Валидация клиента (версия 2).
 *
 * @api https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/validateClientUsingPOST
 */
class ValidateClientRequest extends MonoPartsRequest
{
    /** @var string телефон клиента */
    public $phone;

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['phone', 'trim'],
            ['phone', 'required'],
            ['phone', PhoneValidator::class]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function url(): string
    {
        return 'v2/client/validate';
    }

    /**
     * @inheritDoc
     */
    protected function data(): array
    {
        return [
            'phone' => $this->phone
        ];
    }

    /**
     * Отправка запроса.
     *
     * @return ValidateClientResponse
     * @throws Exception
     */
    public function send(): ValidateClientResponse
    {
        return new ValidateClientResponse($this, [
            'data' => parent::send()
        ]);
    }
}
