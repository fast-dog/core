<?php

namespace FastDog\Core\Models;

use FastDog\Core\Interfaces\BaseModelInterface;
use FastDog\Core\Interfaces\BaseModelStateInterface;
use FastDog\Core\Traits\StateTrait;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use FastDog\Core\Properties\Traits\PropertiesTrait as PropertiesTrait;
use Illuminate\Support\Str;

/**
 * Реализация базовой модели
 *
 * Поддержка состояний, базовое заполнение свойств
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseModel extends Model implements BaseModelStateInterface, BaseModelInterface
{
    use SoftDeletes, StateTrait, PropertiesTrait;

    /**
     * Массив полей автозаполнения
     * @var array $fillable
     */
    protected $fillable = [self::NAME, self::ALIAS, self::DATA, self::SITE_ID, self::TYPE];

    /**
     * Массив полей для обработки даты
     * @var array $dates
     */
    public $dates = ['deleted_at'];

    /**
     * Возможные состояния модели
     *
     * Возвращает массив с возможными состояниями базовой модели
     * <pre>
     * [
     *  ['id' => self::STATE_PUBLISHED, 'name' => 'Опубликовано'],
     *  ['id' => self::STATE_NOT_PUBLISHED, 'name' => 'Не опубликовано'],
     *  ['id' => self::STATE_IN_TRASH, 'name' => 'В корзине'],
     * ]
     * </pre>
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            ['id' => self::STATE_PUBLISHED, 'name' => trans('core::interface.states.published')],
            ['id' => self::STATE_NOT_PUBLISHED, 'name' => trans('core::interface.states.published')],
            ['id' => self::STATE_IN_TRASH, 'name' => trans('core::interface.states.in_trash')],
        ];
    }


    /**
     * Медиа материалы
     *
     * Возвращает набор медиа данных модели
     *
     * @return Collection
     */
    public function getMedia(): Collection
    {
        return collect([]);
    }

    /**
     * Возвращает общую информацию о текущей модели
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            'id' => $this->id,
            self::NAME => $this->{self::NAME},
            self::ALIAS => $this->{self::ALIAS},
            self::STATE => $this->{self::STATE},
            self::SITE_ID => $this->{self::SITE_ID},
            self::CREATED_AT => $this->{self::CREATED_AT},
            self::UPDATED_AT => $this->{self::UPDATED_AT},
            self::DELETED_AT => $this->{self::DELETED_AT},
        ];
    }


    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент
     * @return string
     */
    public function getEventPrepareName(): string
    {
        return 'Event' . Str::studly(class_basename($this)) . 'Prepared';
    }


    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент в разделе администрирования
     * @return string
     */
    public function getEventAdminPrepareName(): string
    {
        return Str::studly(class_basename($this)) . 'AdminPrepare';
    }

    /**
     * Дополнительный фильтр таблицы
     * @param Builder $query
     * @return Builder
     */
    public function setFilters(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Имена упакованных в json объект данных модели,
     * вызывается при извлечение/упаковки этих данных в объект data,
     * используется в FastDog\Core\Listeners\ModelBeforeSave
     *
     * @return array
     */
    public function getExtractParameterNames(): array
    {
        return [];
    }

    /**
     * Отношение к домену\сайту
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function domain()
    {
        return $this->hasOne(Domain::class, Domain::CODE, self::SITE_ID);
    }

    /**
     * Идентификатор модели
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Маршрут по умолчанию
     * @return string
     */
    public function getRoute(): string
    {
        return $this->id . '-' . $this->{self::ALIAS} . '.html';
    }
}
