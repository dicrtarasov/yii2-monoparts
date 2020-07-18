<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 19.07.20 03:47:23
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\validate\ValidateException;
use yii\base\DynamicModel;
use yii\base\Exception;
use yii\httpclient\Client;
use function array_map;
use function array_unique;
use function gettype;
use function is_array;
use function preg_match;
use const SORT_NUMERIC;

/**
 * Заявка на создание оплаты частями.
 *
 * @link https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/createOrderUsingPOST
 */
class PaymentRequest extends AbstractRequest
{
    /** @var string [32] номер заказа магазина */
    public $storeOrderId;

    /** @var string +380\d{9} Телефон клиента */
    public $clientPhone;

    /** @var float|null Должно быть 2 знака после точки */
    public $sum;

    /** @var string Дата чека/счета фактуры. Формат: yyyy-MM-dd */
    public $invoiceDate;

    /** @var string [2147483647] Номер чека/счета фактуры */
    public $invoiceNum;

    /** @var string|null [50] идентификатор торговой точки */
    public $pointId;

    /** @var string Канал приема платежа */
    public $source = self::SOURCE_INTERNET;

    /** @var int[] возможные варианты кол-ва частей, на которые можно разбить платеж */
    public $partsCount;

    /** @var string */
    public $programType = self::PROGRAM_TYPE;

    /**
     * @var array товары
     * - string $name [500] название товара
     * - float $price цена
     * - int $quantity кол-во
     */
    public $prods;

    /** @var string URL ответа */
    public $callback;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['storeOrderId', 'trim'],
            ['storeOrderId', 'required'],
            ['storeOrderId', 'string', 'max' => 32],

            ['clientPhone', 'trim'],
            ['clientPhone', 'required'],
            ['clientPhone', PhoneValidator::class],

            ['invoiceDate', 'trim'],
            ['invoiceDate', 'default', 'value' => date('Y-m-d')],
            ['invoiceDate', 'date', 'format' => 'php:Y-m-d'],

            ['invoiceNum', 'trim'],
            ['invoiceNum', 'default', 'value' => function() {
                return $this->storeOrderId;
            }],
            ['invoiceNum', 'string', 'max' => 2 ** 31 - 1],

            ['pointId', 'trim'],
            ['pointId', 'default'],
            ['pointId', 'string', 'max' => 50],

            ['source', 'required'],
            ['source', 'in', 'range' => [self::SOURCE_STORE, self::SOURCE_INTERNET]],

            ['partsCount', function($attribute) {
                $this->{$attribute} = array_unique((array)($this->{$attribute} ?: []));
                if (empty($this->{$attribute})) {
                    $this->addError($attribute, 'Требуется указать варианты кол-ва платежей');
                } else {
                    $this->{$attribute} = array_unique(array_map(function($count) use ($attribute) {
                        if ($count < 2 || ! preg_match('~^\d+$~u', (string)$count)) {
                            $this->addError($attribute, 'Некорректное кол-во: ' . $count);
                        } else {
                            $count = (int)$count;
                        }

                        return $count;
                    }, $this->{$attribute}), SORT_NUMERIC);

                    sort($this->{$attribute}, SORT_NUMERIC);
                }
            }],

            ['programType', 'trim'],
            ['programType', 'default', 'value' => self::PROGRAM_TYPE],

            ['prods', 'required'],
            ['prods', function($attribute) {
                if (empty($this->{$attribute})) {
                    $this->addError($attribute, 'Требуется список товаров');
                } elseif (is_array($this->{$attribute})) {
                    $this->{$attribute} = array_map(function($prod) use ($attribute) {
                        if (is_array($prod)) {
                            $model = DynamicModel::validateData($prod, [
                                ['name', 'trim'],
                                ['name', 'required'],
                                ['name', 'string', 'max' => 500],

                                ['price', 'required'],
                                ['price', 'number', 'min' => 0.01],

                                ['quantity', 'required'],
                                ['quantity', 'integer', 'min' => 1]
                            ]);

                            if ($model->hasErrors()) {
                                $this->addError($attribute);
                            } else {
                                $prod = $model->attributes;
                            }
                        } else {
                            $this->addError($attribute, 'Тип товара: ' . gettype($prod));
                        }

                        return $prod;
                    }, $this->{$attribute});
                } else {
                    $this->addError($attribute, 'Некорректный формат: ' . gettype($this->{$attribute}));
                }
            }],

            ['sum', 'trim'],
            ['sum', 'default', 'value' => function() {
                return array_reduce($this->prods, static function(float $sum, array $prod) {
                    return $sum + $prod['price'] * $prod['quantity'];
                }, 0);
            }],
            ['sum', 'number', 'min' => MonopartsModule::SUM_MIN],

            ['callback', 'trim'],
            ['callback', 'default'],
            ['callback', 'url']
        ];
    }

    /**
     * Данные для JSON.
     *
     * @return array
     * @throws ValidateException
     */
    protected function toJson()
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        return array_filter([
            'store_order_id' => (string)$this->storeOrderId,
            'client_phone' => (string)$this->clientPhone,
            'total_sum' => sprintf('%.2f', $this->sum),
            'invoice' => array_filter([
                'date' => (string)$this->invoiceDate,
                'number' => (string)$this->invoiceNum,
                'point_id' => (string)$this->pointId,
                'source' => (string)$this->source
            ], static function($val) {
                return $val !== '';
            }),
            'available_programs' => [
                [
                    'available_parts_count' => $this->partsCount,
                    'type' => $this->programType
                ]
            ],
            'products' => array_map(static function(array $prod) {
                return [
                    'name' => (string)$prod['name'],
                    'count' => (int)$prod['quantity'],
                    'sum' => sprintf('%.2f', $prod['price']),
                ];
            }, $this->prods),
            'result_callback' => (string)$this->callback
        ], static function($val) {
            return $val !== null && $val !== '' && $val !== [];
        });
    }

    /**
     * Отправляет запрос.
     *
     * @return string присвоенный номер заявки
     * @throws Exception
     * @throws ValidateException
     * @throws \yii\httpclient\Exception
     */
    public function send()
    {
        $request = $this->module->httpClient->post('/api/order/create', $this->toJson());

        $request = $this->signRequest($request);

        $response = $request->send();
        $response->format = Client::FORMAT_JSON;

        if (! $response->isOk) {
            throw new Exception('Ошибка отправки запроса: ' . $response->data['message'] ?? $response->toString());
        }

        // номер заявки на оплату
        $paymentId = $response->data['order_id'] ?? null;
        if (empty($paymentId)) {
            throw new Exception('Не получен номер заявки от банка: ' . $response->toString());
        }

        return $paymentId;
    }
}
