<?php
/**
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 18.07.20 22:56:39
 */

declare(strict_types = 1);
namespace dicr\monoparts;

/**
 * Константы.
 */
interface Monoparts
{
    /** @var string */
    public const API_URL = 'https://u2.monobank.com.ua';

    /** @var string */
    public const TEST_URL = 'https://u2-demo-ext.mono.st4g3.com';

    /** @var string */
    public const TEST_STORE_ID = 'test_store_with_confirm';

    /** @var string */
    public const TEST_KEY = 'secret_98765432--123-123';

    /** @var int минимальная сумма платежа */
    public const SUM_MIN = 500;

    /** @var string канал приема платежа - магазин */
    public const SOURCE_STORE = 'STORE';

    /** @var string канал приема платежа - сайт в интернете */
    public const SOURCE_INTERNET = 'INTERNET';

    /** @var string программа рассрочки */
    public const PROGRAM_TYPE = 'payment_installments';

    /**
     * @var string успешно.
     * SubSTATE: ACTIVE, DONE, RETURNED
     */
    public const STATE_SUCCESS = 'SUCCESS';

    /**
     * @var string в процессе
     * SubSTATE: WAITING_FOR_CLIENT, WAITING_FOR_STORE_CONFIRM
     */
    public const STATE_IN_PROCESS = 'IN_PROCESS';

    /**
     * @var string ошибка
     * SubSTATE: CLIENT_NOT_FOUND, EXCEEDED_SUM_LIMIT, EXISTS_OTHER_OPEN_ORDER, FAIL, NOT_ENOUGH_MONEY_FOR_INIT_DEBIT
     * REJECTED_BY_CLIENT, CLIENT_PUSH_TIMEOUT, REJECTED_BY_STORE
     */
    public const STATE_FAIL = 'FAIL';

    /**
     * @var string заявка успешная, товар передан клиенту, деньги отправлены магазину.
     * Финальный статус по заявке
     */
    public const SUB_STATE_ACTIVE = 'ACTIVE';

    /** @var string заявка успешная, товар передан клиенту, деньги отправлены магазину, ПЧ погашен клиентом. */
    public const SUB_STATE_DONE = 'DONE';

    /** @var string магазином принят возврат товара, деньги перечислены клиенту */
    public const SUB_STATE_RETURNED = 'RETURNED';

    /** @var string ожидания подтверждения от клиента кредитного договора в приложении монобанк */
    public const SUB_STATE_WAITING_FOR_CLIENT = 'WAITING_FOR_CLIENT';

    /**
     * @var string кредитная сделка ПЧ подтверждена клиентом.
     * Важно! ключевой статус, после получения которого необходимо передать товар клиенту
     */
    public const SUB_STATE_WAITING_FOR_STORE_CONFIRM = 'WAITING_FOR_STORE_CONFIRM';

    /**
     * @var string Клиент не найден.
     * Варианты: не клиент монобанка; указан не финансовый номер
     */
    public const SUB_STATE_CLIENT_NOT_FOUND = 'CLIENT_NOT_FOUND';

    /**
     * @var string Клиент превысил допустимый лимит на ПЧ.
     * Лимит можно посмотреть в приложении монобанк в меню Рассрочка.
     */
    public const SUB_STATE_EXCEEDED_SUM_LIMIT = 'EXCEEDED_SUM_LIMIT';

    /**
     * @var string У клиента есть другая открытая заявка на ПЧ.
     * Решение: отменить открытую заявку в приложении клиентом или магазином методом reject;
     * подождать 15 мин, заявка перейдет в статус CLIENT_PUSH_TIMEOUT
     */
    public const SUB_STATE_EXISTS_OTHER_OPEN_ORDER = 'EXISTS_OTHER_OPEN_ORDER';

    /**
     * @var string Внутренняя ошибка на стороне Банка.
     * Рекомендуем повторить подачу заявки через 5 мин.
     */
    public const SUB_STATE_FAIL = 'FAIL';

    /**
     * @var string Недостаточно средств для первого списания.
     * Решение: пополнить карту монобанка на сумму первого платежа
     */
    public const SUB_STATE_NOT_ENOUGH_MONEY_FOR_INIT_DEBIT = 'NOT_ENOUGH_MONEY_FOR_INIT_DEBIT';

    /** @var string Клиент отказался от совершения покупки */
    public const SUB_STATE_REJECTED_BY_CLIENT = 'REJECTED_BY_CLIENT';

    /**
     * @var string Клиент не принял решение по кредитному договору ПЧ в приложении монобанка.
     * Кредитный договор активен 15 мин.
     * Решение: связаться с клиентом; повторить заявку
     */
    public const SUB_STATE_CLIENT_PUSH_TIMEOUT = 'CLIENT_PUSH_TIMEOUT';
}
