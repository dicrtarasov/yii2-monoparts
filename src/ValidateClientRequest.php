<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 18.07.20 20:59:51
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\validate\ValidateException;
use yii\base\Exception;
use yii\httpclient\Client;
use function preg_match;
use function preg_replace;

/**
 * Валидация клиента (версия 2).
 *
 * @api https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/validateClientUsingPOST
 */
class ValidateClientRequest extends AbstractRequest
{
    /** @var string телефон клиента */
    public $phone;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['phone', 'trim'],
            ['phone', 'required'],
            ['phone', function($attribute) {
                // удаляем все не числа
                $this->{$attribute} = preg_replace('~[\D]+~u', '', (string)$this->{$attribute});

                // удаляем ^380 если есть
                $this->{$attribute} = preg_replace('~^380~u', '', $this->{$attribute});

                // добавляем +380
                $this->{$attribute} = '+380' . $this->{$attribute};

                // проверяем формат
                if (! preg_match('~^\+380\d{9}$~', $this->{$attribute})) {
                    $this->addError($attribute, 'Некорректный телефон');
                }
            }]
        ];
    }

    /**
     * Данные JSON.
     *
     * @return string[]
     * @throws ValidateException
     */
    protected function toJson()
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        return [
            'phone' => (string)$this->phone
        ];
    }

    /**
     * Отправка запроса.
     *
     * @return bool true, если клиент найден в базе банка
     * @throws Exception
     * @throws ValidateException
     * @throws \yii\httpclient\Exception
     */
    public function send()
    {
        $request = $this->module->httpClient->post('/api/v2/client/validate', $this->toJson());
        $request = $this->signRequest($request);

        $response = $request->send();
        $response->format = Client::FORMAT_JSON;

        if (! $response->isOk) {
            throw new Exception('Ошибка запроса: ' . $response->data['message'] ?? $response->toString());
        }

        if (! isset($response->data['found'])) {
            throw new Exception('Неизвестный формат ответа: ' . $response->toString());
        }

        return (bool)$response->data['found'];
    }
}
