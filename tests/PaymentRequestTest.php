<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 24.08.20 03:06:28
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\monoparts\MonoParts;
use dicr\monoparts\Product;
use yii\base\Exception;

use function time;

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
     * @throws Exception
     */
    public function testSend()
    {
        $storeOrderId = (string)time();

        // отправляем заявку на платеж
        $request = $this->module()->createOrderCreateRequest([
            'storeOrderId' => $storeOrderId,
            'clientPhone' => MonoParts::TEST_PHONE,
            'partsCount' => [MonoParts::TEST_PARTS_COUNT],
            'products' => [
                new Product(['name' => 'Тест', 'sum' => 123.45, 'count' => 1]),
                new Product(['name' => 'Тест', 'sum' => 400, 'count' => 2])
            ]
        ]);

        $response = $request->send();
        self::assertNotEmpty($response->orderId);
        echo 'OrderId: ' . $response->orderId . "\n";

        // проверяем состояние платежа
        $request = $this->module()->createOrderStateRequest([
            'orderId' => $response->orderId
        ]);

        $response = $request->send();
        self::assertSame(MonoParts::STATE_SUCCESS, $response->state);
        echo 'State: ' . $response->state . "\n";

        self::assertSame(MonoParts::SUB_STATE_ACTIVE, $response->subState);
        echo 'SubState: ' . $response->subState . "\n";
    }
}
