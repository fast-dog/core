<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 05.04.2018
 * Time: 17:58
 */

namespace FastDog\Core\Properties;


use Baum\Extensions\Eloquent\Model;

/**
 * Class BasePropertiesStorage
 * @package FastDog\Core\Properties
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class BasePropertiesStorage extends Model
{
    /**
     * Идентификатор типа модели
     * @cont string
     */
    const MODEL_ID = 'model_id';

    /**
     * Идентификатор модели в базе данных
     * @cont string
     */
    const ITEM_ID = 'item_id';

    /**
     * Идентификатор доп. параметра
     * @cont string
     */
    const PROPERTY_ID = 'property_id';

    /**
     * Значение свойства
     * @cont string
     */
    const VALUE = 'value';

    /**
     * @var bool $timestamps
     */
    public $timestamps = false;

    /**
     * @var string $table
     */
    public $table = 'system_properties_store';

    /**
     * @var array $fillable
     */
    public $fillable = [self::MODEL_ID, self::ITEM_ID, self::VALUE, self::PROPERTY_ID];


}