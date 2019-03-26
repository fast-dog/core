<?php

namespace FastDog\Core\Models;


use FastDog\Core\Interfaces\ModuleInterface;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Реализация формирования меню в разделе администратора
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AdminMenu extends Model
{


    /**
     * Структура меню
     *
     * Метод возвращает структуру меню в зависимости от установленных модулей
     *
     * @return array
     */
    public static function get()
    {
        /**
         * @var $moduleManager ModuleManager
         */
        $moduleManager = \App::make(ModuleManager::class);

        $result = [];

        $modules = Module::where(function (Builder $query) {
            //  $query->where(BaseModel::STATE, BaseModel::STATE_PUBLISHED);
        })->orderBy(Module::PRIORITY, 'asc')->get();

        $siteId = DomainManager::getSiteId();

        foreach ($modules as $module) {
            $data = json_decode($module->{Module::DATA});

            /**
             * @var $instance ModuleInterface
             *
             * Проверка динамически формируемых списокок меню,
             * контроль доступа в жанном случае осуществляет поставщик данных,
             * например DataSource
             */
            $instance = $moduleManager->getInstance($data->source->class);

            if (method_exists($instance, 'getAdminMenuItems')) {
                //проверка доступа
                if (self::checkAccess($siteId, (array)$data->route->access)) {

                    $tmp = [];
                    // array_push($tmp, array_first($data->route->children));
                    $routes = $instance->getAdminMenuItems();
                    foreach ($routes as $route) {
                        array_push($tmp, $route);
                    }
                    //  array_push($tmp, array_last($data->route->children));
                    $data->route->children = $tmp;
                    array_push($result, $data->route);
                }
            }
        }

        return $result;
    }

    /**
     * Проверка доступа к элементам меню
     *
     * @param string $siteId код сайта в формате ХХХ
     * @param array $accessList массив кодов сайта которым разрешен доступ к меню, определяется в файле module.json
     *
     * @return bool
     */
    public static function checkAccess($siteId, $accessList)
    {
        /**
         * Общий доступ к элементу открыт
         */
        if (in_array("000", $accessList)) {
            return true;
        }

        /**
         * Доступ сайта к элементу открыт
         */
        if (in_array($siteId, $accessList)) {
            return true;
        }

        /**
         * По умолчанию доступ закрыт
         */
        return false;
    }
}
