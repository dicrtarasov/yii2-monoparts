<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 03.11.20 19:51:37
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\monoparts\MonoParts;
use yii\base\Exception;

/**
 * Class ValidateClientRequestTest
 */
class ValidateClientRequestTest extends AbstractTest
{
    /**
     * Тест телефона покупателя.
     *
     * @throws Exception
     */
    public function testSend() : void
    {
        $request = $this->module()->validateClientRequest([
            'phone' => MonoParts::TEST_PHONE
        ]);

        $response = $request->send();

        self::assertTrue($response->found);
    }
}
