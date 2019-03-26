<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 17.01.2017
 * Time: 12:31
 */

namespace FastDog\Core\Models;

use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


/**
 * Обработчик ошибок
 *
 * Сохранение ошибок во время выполнения в базу данных
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ErrorLog extends Model
{
    /**
     * Идентификатор пользователя
     *
     * @const int
     */
    const USER_ID = 'user_id';

    /**
     * Подробные сведения о ошибке
     *
     * Объект json в произволном виде
     */
    const DATA = 'data';

    /**
     * Имя таблицы в базе данных
     * @var string $table 'system_error_log'
     */
    public $table = 'system_error_log';
    /**
     * Массив полей автозаполнения
     *
     * @var array [self::DATA, self::USER_ID]
     */
    public $fillable = [self::DATA, self::USER_ID];

}
