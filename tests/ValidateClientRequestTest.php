<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 19.07.20 02:23:21
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\monoparts\Monoparts;
use dicr\validate\ValidateException;
use yii\httpclient\Exception;

/**
 * Class ValidateClientRequestTest
 */
class ValidateClientRequestTest extends AbstractTest
{
    /**
     * Тест телефона покупателя.
     *
     * @throws ValidateException
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function testSend()
    {
        $request = $this->module()->createValidateClientRequest([
            'phone' => Monoparts::TEST_PHONE
        ]);

        self::assertTrue($request->send());
    }
}
