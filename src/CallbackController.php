<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 18.07.20 22:51:28
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use function call_user_func;

/**
 * Контроллер обработки запросов от банка.
 *
 * @property-read MonopartsModule $module
 */
class CallbackController extends Controller
{
    /**
     * Обработка результатов оплаты (запросы от банка).
     *
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        if (! Yii::$app->request->isPost) {
            throw new BadRequestHttpException();
        }

        // проверяем сигнатуру
        $signature = $this->module->signature(Yii::$app->request->getRawBody());
        if (Yii::$app->request->headers->get('signature') !== $signature) {
            throw new BadRequestHttpException('signature');
        }

        $paymentState = new StateResponse(Yii::$app->request->getBodyParams());
        $paymentId = $paymentState->order_id;

        // получаем номер заказа магазина по номеру заявки на оплату
        $orderId = $this->module->loadPaymentOrder($paymentId);
        $this->module->deletePaymentOrder($paymentId);

        if ($paymentState->state === StateResponse::STATE_SUCCESS) {
            Yii::info('Успешная оплата №' . $paymentId . ' заказа №' . $orderId, __METHOD__);

            if (! empty($this->module->paymentHandler)) {
                call_user_func($this->module->paymentHandler, $paymentId, $orderId);
            }
        } elseif ($paymentState->state === StateResponse::STATE_FAIL) {
            Yii::warning('Ошибка оплаты №' . $paymentId . ' заказа №' . $orderId . ': ' .
                $paymentState->order_sub_state, __METHOD__);

        } else {
            throw new BadRequestHttpException('Некорректное состояние оплаты: ' . $paymentState->state);
        }
    }
}
