<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:51:37
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use yii\base\Model;

use function round;

/**
 * Информация о товаре.
 *
 * @property-read array $data данные JSON
 */
class Product extends Model
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
            ['sum', 'filter', 'filter' => static function ($sum) : float {
                return round((float)$sum, 2);
            }]
        ];
    }

    /**
     * Данные JSON.
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            'name' => $this->name,
            'count' => $this->count,
            'sum' => sprintf('%.2f', $this->sum)
        ];
    }
}
