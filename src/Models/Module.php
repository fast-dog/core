<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 01.11.2016
 * Time: 23:58
 */

namespace FastDog\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель хранения данных о установленных модулях
 *
 * @package FastDog\Core\Module
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Module extends Model
{
    /**
     * Название модуля
     * @const string
     */
    const NAME = 'name';
    /**
     * Дополнительная информация
     * @const string
     */
    const DATA = 'data';
    /**
     * Версия
     * @const string
     */
    const VERSION = 'version';
    /**
     * Приоритет отображения
     *
     * Приоритет отображения в разделе администрирования
     * @const string
     */
    const PRIORITY = 'priority';

    /**
     * Имя таблицы в базе данных
     *
     * @var string $table 'modules'
     */
    public $table = 'core_modules';

    /**
     * Массив полей автозаполнения
     *
     * @var array $fillable [self::NAME, self::DATA, self::VERSION, self::PRIORITY]
     */
    public $fillable = [self::NAME, self::DATA, self::VERSION, self::PRIORITY];

}