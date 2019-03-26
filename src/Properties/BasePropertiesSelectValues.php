<?php

namespace FastDog\Core\Properties;

use Illuminate\Database\Eloquent\Model;

/**
 * Варианты значений для параметров с типом - список
 *
 * @package FastDog\Core\Properties
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BasePropertiesSelectValues extends Model
{
    /**
     * Название параметра
     *
     * @const string
     */
    const NAME = 'name';

    /**
     * Псевдоним параметра
     *
     * @const string
     */
    const ALIAS = 'alias';

    /**
     * Идентификатор свойства
     *
     * @const string
     */
    const PROPERTY_ID = 'property_id';

    /**
     * @var string $table
     */
    public $table = 'system_properties_select_values';

    /**
     * @var bool $timestamps
     */
    public $timestamps = false;

    /**
     * @var array $fillable
     */
    public $fillable = [self::NAME, self::ALIAS, self::PROPERTY_ID];

    /**
     * @return array
     */
    public function getData(): array
    {
        $result = [
            'id' => $this->id,
            self::NAME => $this->{self::NAME},
            self::ALIAS => $this->{self::ALIAS},
            self::PROPERTY_ID => $this->{self::PROPERTY_ID},
        ];

        return $result;
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        BasePropertiesStorage::where([
            BasePropertiesStorage::PROPERTY_ID => $this->{self::PROPERTY_ID},
            BasePropertiesStorage::VALUE => $this->id,
        ])->delete();

        return parent::delete();
    }
}