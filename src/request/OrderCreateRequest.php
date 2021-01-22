<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:19:23
 */

declare(strict_types = 1);
namespace dicr\monoparts\request;

use dicr\json\EntityValidator;
use dicr\monoparts\MonoPartsModule;
use dicr\monoparts\MonoPartsRequest;
use dicr\monoparts\PhoneValidator;
use dicr\monoparts\Product;
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
    public function rules(): array
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
            ['invoiceNum', 'default', 'value' => fn(): string => $this->storeOrderId],
            ['invoiceNum', 'string', 'max' => 2 ** 31 - 1],

            ['pointId', 'trim'],
            ['pointId', 'default'],
            ['pointId', 'string', 'max' => 50],

            ['source', 'default', 'value' => self::SOURCE_INTERNET],
            ['source', 'in', 'range' => [self::SOURCE_STORE, self::SOURCE_INTERNET]],

            ['partsCount', function($attribute) {
                if (empty($this->{$attribute})) {
                    $this->addError($attribute, 'Требуется указать варианты кол-ва платежей');
                } elseif (is_array($this->{$attribute})) {
                    $this->{$attribute} = array_unique(array_map(function($count) use ($attribute): int {
                        if ($count < 2 || ! preg_match('~^\d+$~u', (string)$count)) {
                            $this->addError($attribute, 'Некорректное кол-во: ' . $count);
                        } else {
                            $count = (int)$count;
                        }

                        return $count;
                    }, (array)($this->{$attribute} ?? [])), SORT_NUMERIC);

                    sort($this->{$attribute}, SORT_NUMERIC);
                } else {
                    $this->addError($attribute, 'Должен быть массивом');
                }
            }],

            ['programType', 'trim'],
            ['programType', 'default', 'value' => self::PROGRAM_TYPE],

            ['products', 'required'],
            ['products', EntityValidator::class, 'class' => [Product::class]],

            // после проверки товаров
            ['sum', 'trim'],
            ['sum', 'default', 'value' => fn() => array_reduce(
                $this->products,
                static fn(float $sum, Product $prod): float => $sum + $prod->count * $prod->sum,
                0
            )],
            ['sum', 'number', 'min' => MonoPartsModule::SUM_MIN],
            ['sum', 'filter', 'filter' => static fn($sum): float => round((float)$sum, 2)],

            ['callback', 'trim'],
            ['callback', 'default'],
            ['callback', 'url']
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeEntities(): array
    {
        return array_merge(parent::attributeEntities(), [
            'products' => [Product::class]
        ]);
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
    public function getJson(): array
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
            ], static fn($val): bool => $val !== null && $val !== ''),
            'available_programs' => [
                [
                    'available_parts_count' => $this->partsCount,
                    'type' => $this->programType
                ]
            ],
            'products' => array_map(
                static fn(Product $prod): array => $prod->json,
                $this->products
            ),
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
        return new OrderCreateResponse([
            'json' => parent::send()
        ]);
    }
}
