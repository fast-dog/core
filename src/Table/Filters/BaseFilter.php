<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.03.2018
 * Time: 17:50
 */

namespace FastDog\Core\Table\Filters;


use FastDog\Core\Models\BaseModel;
use FastDog\Core\Table\Filters\Logic\BaseLogic;
use FastDog\Core\Table\Filters\Operator\BaseOperator;

/**
 * Реализация фильтра по умолчанию для таблиц
 *
 * @package FastDog\Core\Table\Filters
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseFilter extends BaseModel
{
    /**
     * Условный оператор
     * @const string
     */
    const OPERATOR = 'operator';

    /**
     * Отображение в форме
     * @const string
     */
    const DISPLAY = 'display';

    /**
     * Сортировка
     * @const string
     */
    const SORT = 'sort';

    /**
     * Правило валидации на уровне интерфейса
     * @const string
     */
    const VALIDATE = 'validate';

    /**
     * Подпись фильтра в поле ввода
     * @const string
     */
    const PLACEHOLDER = 'placeholder';

    /**
     * Множественный выбор в поле типа select
     * @const string
     */
    const MULTIPLE = 'multiple';

    /**
     * Значение фильтра
     * @const string
     */
    const VALUE = 'value';
    /**
     * Тип поля - текст
     * @const string
     */
    const TYPE_TEXT = 'text';

    /**
     * Тип поля - выпадающий список
     * @const string
     */
    const TYPE_SELECT = 'select';

    /**
     * Тип поля - период времени
     * @const string
     */
    const TYPE_DATETIME = 'date';

    /**
     * Тип поля - условный оператор
     * @const string
     */
    const TYPE_OPERATOR = 'operator';

    /**
     * Тип поля - логический оператор
     * @const string
     */
    const TYPE_LOGIC = 'logic';

    /**
     * @var string $id
     */
    protected $id = '';

    /**
     * @var string $type
     */
    protected $type = '';

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var string $placeholder
     */
    protected $placeholder = '';

    /**
     * @var string $name
     */
    protected $name = '';

    /**
     * @var bool $multiple
     */
    protected $multiple = false;

    /**
     * @var null|BaseOperator
     */
    protected $operator = null;

    public static function getFiltersByName()
    {

    }

    public function createFilter($data)
    {

    }

    /**
     * @return array
     */
    public static function getLogicAnd(): array
    {
        return (new BaseLogic('AND', 'AND', 'Логическое условие И'))->getLogic();
    }

    /**
     * @return array
     */
    public static function getLogicOr(): array
    {
        return [
            BaseFilter::TYPE => BaseFilter::TYPE_LOGIC,
            BaseFilter::DATA => json_encode([
                BaseFilter::OPERATOR => new BaseLogic('OR', 'OR', 'Логическое условие ИЛИ'),
                BaseFilter::DISPLAY => false,
            ]),
        ];
    }
}