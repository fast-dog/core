<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.03.2018
 * Time: 19:57
 */

namespace FastDog\Core\Table\Interfaces;

/**
 * Таблица в разделе администрирования
 *
 * Для реализации в модели данных
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface TableModelInterface
{
    /**
     * Возвращает описание доступных полей для вывода в колонки...
     *
     * ... метод используется для первоначального конфигурирования таблицы,
     * дальнейшие типы, порядок колонок и т.д. будут храниться в обхекте BaseTable
     *
     * [
     *      'name' => trans('name'),
     *      'key' => self::NAME,
     *      'domain' => true,
     *      'callback' => false,
     *      'link' => 'link_item',
     *      'extra' => true,
     *      'action' => [
     *              'edit' => true,
     *              'replicate' => true,
     *              'delete' => true,
     *      ]
     * ],
     *
     * @return array
     */
    public function getTableCols(): array;

    /**
     * Определение фильтров таблицы в виде массива
     *
     * @return array
     */
    public function getAdminFilters(): array;

}
