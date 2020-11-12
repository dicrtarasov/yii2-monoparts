<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 12.11.20 06:15:59
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use dicr\monoparts\request\OrderStateResponse;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

use function call_user_func;

/**
 * Контроллер обработки запросов от банка.
 *
 * @property-read MonoPartsModule $module
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
     */
    public function actionIndex() : void
    {
        if (! Yii::$app->request->isPost) {
            throw new BadRequestHttpException();
        }

        // проверяем сигнатуру
        $signature = $this->module->signature(Yii::$app->request->rawBody);
        if (Yii::$app->request->headers->get('signature') !== $signature) {
            throw new BadRequestHttpException('signature');
        }

        Yii::debug('Monoparts callback: ' . Yii::$app->request->rawBody, __METHOD__);

        if (! empty($this->module->handler)) {
            $response = new OrderStateResponse(Yii::$app->request->bodyParams);
            call_user_func($this->module->handler, $response);
        }
    }
}
