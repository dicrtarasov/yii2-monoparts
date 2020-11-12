<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 05:54:20
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\json\JsonEntity;

/**
 * Информация о товаре.
 *
 * @property-read array $data данные JSON
 */
class Product extends JsonEntity
{
    /** @var string */
    public $name;

    /** @var int */
    public $count;

    /** @var float цена за единицу товара (должно быть 2 знака после точки) */
    public $sum;

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['name', 'trim'],
            ['name', 'required'],
            ['name', 'string', 'max' => 500],

            ['count', 'required'],
            ['count', 'integer', 'min' => 1],
            ['count', 'filter', 'filter' => 'intval'],

            ['sum', 'required'],
            ['sum', 'number', 'min' => 0.01],
            ['sum', 'filter', 'filter' => static function ($val) : string {
                return sprintf('%.2f', (float)$val);
            }]
        ];
    }
}
