<?php

namespace FastDog\Core\Properties;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Дополниетельные параметры доступные в модели
 *
 * @package FastDog\Core\Properties
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BaseProperties extends Model
{
    use SoftDeletes;

    /**
     * Название параметра
     *
     * @cont string
     */
    const NAME = 'name';

    /**
     * Псевдоним
     *
     * @cont string
     */
    const ALIAS = 'alias';

    /**
     * Значение по умолчанию
     *
     * @cont string
     */
    const VALUE = 'value';

    /**
     * Сортировка по умолчанию
     *
     * @cont string
     */
    const SORT = 'sort';

    /**
     * Тип параметра
     *
     * @cont string
     */
    const TYPE = 'type';

    /**
     * Доанные для формирования вывода в интерфейсе
     *
     * @cont string
     */
    const DATA = 'data';

    /**
     * Модель к которой принадлежит параметр
     *
     * @cont string
     */
    const MODEL = 'model';

    /**
     * Идентификатор модели
     *
     * @cont string
     */
    const ITEM_ID = 'item_id';

    /**
     * Тип параметра - строка
     *
     * @cont string
     */
    const TYPE_STRING = 'string';

    /**
     * Тип параметра - число
     *
     * @cont string
     */
    const TYPE_NUMBER = 'number';

    /**
     * Тип параметра - файл
     *
     * @cont string
     */
    const TYPE_FILE = 'file';

    /**
     * Тип параметра - выпадающий список
     *
     * @cont string
     */
    const TYPE_SELECT = 'select';

    /**
     * Тип параметра - координаты на карте
     *
     * @cont string
     */
    const TYPE_MAP = 'location';

    /**
     * Тип параметра - служебный параметр
     *
     * @cont string
     */
    const TYPE_SYSTEM = 'system';

    /**
     * @var string $table
     */
    public $table = 'system_properties';

    /**
     * @var array $fillable
     */
    public $fillable = [self::NAME, self::ALIAS, self::TYPE, self::DATA, self::MODEL, self::VALUE];

    /**
     * Множественное значения параметра
     *
     * @var bool $multiple
     */
    public $multiple = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|\Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function value()
    {
        $method = ($this->isMultiple()) ? 'hasMany' : 'hasOne';

        return $this->$method(BasePropertiesStorage::class, BasePropertiesStorage::PROPERTY_ID, 'id');
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     */
    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $result = [
            'id' => $this->id,
            self::NAME => $this->{self::NAME},
            self::ALIAS => $this->{self::ALIAS},
            self::TYPE => $this->{self::TYPE},
            self::DATA => (is_string($this->{self::DATA})) ? json_decode($this->{self::DATA}) : $this->{self::DATA},
            self::ITEM_ID => $this->{self::ITEM_ID},
            self::SORT => (int)$this->{self::SORT},
            self::MODEL => (int)$this->{self::MODEL},
            self::VALUE => '',
        ];

        $allowType = [
            ['id' => 'string', 'name' => 'Строка'],
            ['id' => 'number', 'name' => 'Число'],
            ['id' => 'select', 'name' => 'Список'],
            ['id' => 'location', 'name' => 'Координаты'],
            ['id' => 'file', 'name' => 'Файл'],
        ];
        $result[self::TYPE] = array_first(array_filter($allowType, function ($type) use ($result) {
            return $type['id'] == $result[self::TYPE];
        }));

        return $result;
    }

    /**
     * @param int $item_id
     * @return mixed|null
     */
    public function getValue($item_id = 0)
    {
        $check = BasePropertiesStorage::where([
            BasePropertiesStorage::PROPERTY_ID => $this->id,
            BasePropertiesStorage::ITEM_ID => $item_id,
        ])->first();
        if ($check) {
            return $check->{BasePropertiesStorage::VALUE};
        }

        return null;
    }
}