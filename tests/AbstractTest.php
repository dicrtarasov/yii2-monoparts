<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 19.07.20 02:21:27
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\monoparts\MonopartsModule;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * Базовый класс для тестов.
 *
 * Тестовая платформа содержит только 1 магазина и отдает только заранее подготовленные ситуации.
 * store-id: test_store_with_confirm
 * ключ: secret_98765432--123-123
 * link: https://u2-demo-ext.mono.st4g3.com
 *
 * @link https://u2-demo-ext.mono.st4g3.com/docs/index.html#section/Avtorizaciya-(podpis-zaprosov-otvetov)/Testovaya-platforma
 */
abstract class AbstractTest extends TestCase
{
    /**
     * Модуль
     *
     * @return MonopartsModule
     */
    protected function module()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->getModule('monoparts');
    }
}
