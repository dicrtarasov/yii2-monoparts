<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 18.07.20 22:57:10
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\monoparts\Monoparts;
use dicr\monoparts\MonopartsModule;
use dicr\validate\ValidateException;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\httpclient\Exception;

/**
 * Class PaymentRequestTest
 *
 * Тестовая платформа содержит только 1 магазина и отдает только заранее подготовленные ситуации.
 * store-id: test_store_with_confirm
 * ключ: secret_98765432--123-123
 * link: https://u2-demo-ext.mono.st4g3.com
 *
 * @link https://u2-demo-ext.mono.st4g3.com/docs/index.html#section/Avtorizaciya-(podpis-zaprosov-otvetov)/Testovaya-platforma
 */
class PaymentRequestTest extends TestCase
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

    /**
     * Возможные ситуации:
     * - Успешное подтверждение заявки. Для данной ситуации нужно передать номер телефона клиента,
     * который заканчивается на 1. В данном случае через 5 секунд будет брошен callback об успешном утверждении заявки.
     * - Заявка, которая ожидает подтверждения от клиента. Для данной ситуации нужно передать номер телефона клиента,
     * который заканчивается на 2.
     * - Ошибка подтверждения заявки по причине недостаточного лимита у клиента. Для данной ситуации нужно передать
     * номер телефона клиента, который заканчивается на 3. В данном случае через 5 секунд будет брошен callback об
     * отклонении заявки.
     * - Заявка подтверждена клиентом и ожидает подтверждения от магазина (только для магазина с двухэтапным
     * подтверждением). Для данной ситуации нужно передать номер телефона клиента, который заканчивается на 4.
     *
     * @throws ValidateException
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function testSend()
    {
        // отправляем заявку на платеж
        $paymentRequest = $this->module()->createPaymentRequest([
            'storeOrderId' => time(),
            'clientPhone' => '+380500000001',
            'invoiceDate' => date('Y-m-d'),
            'invoiceNum' => time(),
            'partsCount' => [3, 4, 5],
            'prods' => [
                ['name' => 'Тест', 'price' => 123.45, 'quantity' => 1],
                ['name' => 'Тест', 'price' => 400, 'quantity' => 2]
            ],
            'callback' => null
        ]);

        $paymentId = $paymentRequest->send();
        self::assertNotEmpty($paymentId);
        echo 'paymentId: ' . $paymentId . "\n";

        // проверяем состояние платежа
        $stateRequest = $this->module()->createStateRequest([
            'orderId' => $paymentId
        ]);

        $state = $stateRequest->send();
        self::assertSame(Monoparts::STATE_SUCCESS, $state->state);
        self::assertSame(Monoparts::SUB_STATE_ACTIVE, $state->order_sub_state);
    }
}
