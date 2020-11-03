<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:53:49
 */

declare(strict_types = 1);

namespace dicr\monoparts;

use Yii;
use yii\validators\Validator;

use function preg_replace;
use function strlen;
use function substr;

/**
 * Валидатор телефона покупателя.
 *
 * +380\d{9}
 */
class PhoneValidator extends Validator
{
    /**
     * @inheritDoc
     */
    public function validateAttribute($model, $attribute) : void
    {
        // удаляем все, кроме цифр
        $value = preg_replace('~[\D]+~u', '', (string)$model->{$attribute});

        // добавляем 380
        if (strncmp($value, '380', 3) === 0) {
            $value = substr($value, 3);
        }

        if (strlen($value) === 9) {
            $model->{$attribute} = '+380' . $value;
        } else {
            $this->addError($model, $attribute,
                Yii::t('app', 'Некорректный телефон: ' . $model->{$attribute})
            );
        }
    }
}
