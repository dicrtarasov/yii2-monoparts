<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 03:04:58
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\monoparts\MonoPartsModule;
use dicr\monoparts\MonoPartsRequest;
use dicr\monoparts\PhoneValidator;
use dicr\monoparts\Product;
use dicr\validate\ValidateException;
use yii\base\Exception;

use function array_filter;
use function array_map;
use function array_unique;
use function is_array;
use function preg_match;
use function sprintf;

use const SORT_NUMERIC;

/**
 * Заявка на создание оплаты частями.
 *
 * @link https://u2-demo-ext.mono.st4g3.com/docs/index.html#operation/createOrderUsingPOST
 */
class OrderCreateRequest extends MonoPartsRequest
{
    /** @var string [32] номер заказа магазина */
    public $storeOrderId;

    /** @var string +380\d{9} Телефон клиента */
    public $clientPhone;

    /** @var ?string Дата чека/счета фактуры. Формат: yyyy-MM-dd */
    public $invoiceDate;

    /** @var string [2147483647] Номер чека/счета фактуры */
    public $invoiceNum;

    /** @var ?string [50] идентификатор торговой точки */
    public $pointId;

    /** @var string Канал приема платежа */
    public $source = self::SOURCE_INTERNET;

    /** @var int[] возможные варианты кол-ва частей, на которые можно разбить платеж */
    public $partsCount;

    /** @var string */
    public $programType = self::PROGRAM_TYPE;

    /** @var Product[] товары */
    public $products;

    /** @var float Должно быть 2 знака после точки */
    public $sum;

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
            ['invoiceNum', 'default', 'value' => function () {
                return $this->storeOrderId;
            }],
            ['invoiceNum', 'string', 'max' => 2 ** 31 - 1],

            ['pointId', 'trim'],
            ['pointId', 'default'],
            ['pointId', 'string', 'max' => 50],

            ['source', 'default', 'value' => self::SOURCE_INTERNET],
            ['source', 'in', 'range' => [self::SOURCE_STORE, self::SOURCE_INTERNET]],

            ['partsCount', function ($attribute) {
                if (empty($this->{$attribute})) {
                    $this->addError($attribute, 'Требуется указать варианты кол-ва платежей');
                } else {
                    $this->{$attribute} = array_unique(array_map(function ($count) use ($attribute) {
                        if ($count < 2 || ! preg_match('~^\d+$~u', (string)$count)) {
                            $this->addError($attribute, 'Некорректное кол-во: ' . $count);
                        } else {
                            $count = (int)$count;
                        }

                        return $count;
                    }, (array)($this->{$attribute} ?? [])), SORT_NUMERIC);

                    sort($this->{$attribute}, SORT_NUMERIC);
                }
            }],

            ['programType', 'trim'],
            ['programType', 'default', 'value' => self::PROGRAM_TYPE],

            ['products', 'required'],
            ['products', function ($attribute) {
                if (is_array($this->{$attribute})) {
                    foreach ($this->{$attribute} as $prod) {
                        if ($prod instanceof Product) {
                            if (! $prod->validate()) {
                                $this->addError(
                                    $attribute, (new ValidateException($prod))->getMessage()
                                );
                            }
                        } else {
                            $this->addError($attribute, 'Товар должен быть типом Product');
                        }
                    }
                } else {
                    $this->addError($attribute, 'Товары должны быть массивом');
                }
            }],

            ['sum', 'trim'],
            ['sum', 'default', 'value' => function () {
                return array_reduce($this->products, static function (float $sum, Product $prod) {
                    return $sum + $prod->count * $prod->sum;
                }, 0);
            }],
            ['sum', 'number', 'min' => MonoPartsModule::SUM_MIN],
            ['sum', 'filter', 'filter' => static function ($sum) {
                return round((float)$sum, 2);
            }],

            ['callback', 'trim'],
            ['callback', 'default'],
            ['callback', 'url']
        ];
    }

    /**
     * @inheritDoc
     */
    protected function url(): string
    {
        return 'order/create';
    }

    /**
     * @inheritDoc
     */
    protected function data(): array
    {
        return [
            'store_order_id' => $this->storeOrderId,
            'client_phone' => $this->clientPhone,
            'total_sum' => sprintf('%.2f', $this->sum),
            'invoice' => array_filter([
                'date' => $this->invoiceDate,
                'number' => $this->invoiceNum,
                'point_id' => $this->pointId,
                'source' => $this->source
            ], static function ($val) {
                return $val !== null && $val !== '';
            }),
            'available_programs' => [
                [
                    'available_parts_count' => $this->partsCount,
                    'type' => $this->programType
                ]
            ],
            'products' => array_map(static function (Product $prod) {
                return $prod->data;
            }, $this->products),
            'result_callback' => (string)$this->callback
        ];
    }

    /**
     * @inheritDoc
     *
     * @return OrderCreateResponse
     * @throws Exception
     */
    public function send(): OrderCreateResponse
    {
        return new OrderCreateResponse($this, [
            'data' => parent::send()
        ]);
    }
}
