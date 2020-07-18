<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 19.07.20 03:59:09
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
     * @inheritDoc
     * @var bool отключаем CSRF-проверку для запросов банка
     */
    public $enableCsrfValidation = false;

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

        if ($paymentState->state === StateResponse::STATE_SUCCESS) {
            Yii::debug('Успешная оплата №' . $paymentId, __METHOD__);

            if (! empty($this->module->paymentHandler)) {
                call_user_func($this->module->paymentHandler, $paymentId);
            }
        } elseif ($paymentState->state === StateResponse::STATE_FAIL) {
            Yii::warning('Ошибка оплаты №' . $paymentId . ': ' . $paymentState->order_sub_state,
                __METHOD__
            );
        } else {
            throw new BadRequestHttpException('Некорректное состояние оплаты №' . $paymentId . ': ' .
                $paymentState->state
            );
        }
    }
}
