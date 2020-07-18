<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 19.07.20 02:25:51
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\monoparts\Monoparts;
use dicr\validate\ValidateException;
use yii\httpclient\Exception;

/**
 * Class PaymentRequestTest
 */
class PaymentRequestTest extends AbstractTest
{
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
            'clientPhone' => Monoparts::TEST_PHONE,
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

        // проверяем состояние платежа
        $stateRequest = $this->module()->createStateRequest([
            'orderId' => $paymentId
        ]);

        $state = $stateRequest->send();
        self::assertSame(Monoparts::STATE_SUCCESS, $state->state);
        self::assertSame(Monoparts::SUB_STATE_ACTIVE, $state->order_sub_state);
    }
}
