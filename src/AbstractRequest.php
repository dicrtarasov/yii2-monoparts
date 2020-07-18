<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 18.07.20 22:56:38
 */

declare(strict_types = 1);
namespace dicr\monoparts;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\Request;

/**
 * Базовый класс запросов.
 */
abstract class AbstractRequest extends Model implements Monoparts
{
    /** @var MonopartsModule */
    protected $module;

    /**
     * PaymentRequest constructor.
     *
     * @param MonopartsModule $module
     * @param array $config
     */
    public function __construct(MonopartsModule $module, array $config = [])
    {
        if (! $module instanceof MonopartsModule) {
            throw new InvalidArgumentException('module');
        }

        $this->module = $module;

        parent::__construct($config);
    }

    /**
     * Добавляет подпись к запросу.
     *
     * @param Request $request
     * @return Request
     */
    protected function signRequest(Request $request)
    {
        $request->format = Client::FORMAT_JSON;
        $request->headers->set('store-id', $this->module->storeId);
        $request->prepare(); // чтобы сформатировался content из данных в data
        $request->headers->set('signature', $this->module->signature($request->content));

        return $request;
    }
}
