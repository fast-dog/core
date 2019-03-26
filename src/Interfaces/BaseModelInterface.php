<?php

namespace FastDog\Core\Interfaces;

/**
 * Interface BaseModel
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface BaseModelInterface
{
    /**
     * Тип объекта
     *
     * @const string
     */
    const TYPE = 'type';

    /**
     * Идентификатор сайта
     *
     * @const string
     */
    const SITE_ID = 'site_id';

    /**
     * Название объекта
     *
     * @const string
     */
    const NAME = 'name';

    /**
     * Псевдоним объекта
     *
     * @const string
     */
    const ALIAS = 'alias';

    /**
     * Индек сортировки
     *
     * @const int
     */
    const SORT = 'sort';

    /**
     * Идентификатор родительского элемента
     *
     * @const string
     */
    const PARENT_ID = 'parent_id';

    /**
     * Метка удаления
     *
     * @const string
     */
    const DELETED_AT = 'deleted_at';

    /**
     * Дополнительные данные по объекту
     *
     * @const string
     */
    const DATA = 'data';

    /**
     * Значения\по умолчанию
     *
     * @const string
     */
    const VALUES = 'values';

    /**
     * Возвращает общую информацию о текущей модели
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент
     *
     * @return string
     */
    public function getEventPrepareName(): string;

    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент в разделе администрирования
     *
     * @return string
     */
    public function getEventAdminPrepareName(): string;
}