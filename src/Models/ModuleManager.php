<?php
namespace FastDog\Core\Models;


use FastDog\Core\Interfaces\ModuleInterface;
use FastDog\Core\Store;
use Chumper\Zipper\Zipper;
use Illuminate\Support\Collection;

/**
 * Менеджер моудлей
 *
 * Реализация работы с поставляемыми модулями управления контентом.
 * Обеспечивает установку, резервное копирование, переустановку всех типов модулей.
 *
 * @package FastDog\Core\Module
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ModuleManager
{
    /**
     * Массив экземпляров модулей
     * @var array $moduleInstance
     */
    protected $moduleInstance = [];

    /**
     * @param $id
     * @param array $data
     */
    public function pushModule($id, array $data): void
    {
        $this->moduleInstance[$id] = $data;
    }

    /**
     * Проверка существования экземпляра модуля
     *
     * @param string $class
     * @return bool
     */
    public function hasModule($class)
    {
        if (count($this->moduleInstance) == 0) {
            $this->getModules();
        }

        return isset($this->moduleInstance[$class]);
    }

    /**
     * Получение экземпляра модуля
     *
     * В случае если имя экземпляра не задано будет возвращен массив текущих экземпляров
     *
     * @param string|null $class
     * @return mixed|null|array
     */
    public function getInstance($class = null)
    {
        if (count($this->moduleInstance) == 0) {
            $this->getModules();
        }
        if ($class !== null) {
            $instance = isset($this->moduleInstance[$class]) ? $this->moduleInstance[$class] : null;
            if ($instance) {
                $config = $instance->getConfig();
                if (isset($config->name)) {
                    /**  @var $storeManager Store */
                    $storeManager = \App::make(Store::class);
                    $allModules = $storeManager->getCollection(self::class);
                    /**
                     * Методы хранения конфигурации в модулях изменились,
                     * нужно добавить инициатор определения способа корректного определения текущей конфигурации
                     */
                    $item = $allModules->where(Module::NAME, $config->name)->first();
                    if ($item) {
                        $instance->setConfig(json_decode($item->data));
                    }
                }

                return $instance;
            }
        } else {
            return $this->moduleInstance;
        }
    }

    /**
     * Определение списка модулей
     *
     * @return Collection
     */
    public function getModules(): Collection
    {
        return collect($this->moduleInstance);
    }

    /**
     * Список модулей
     *
     * Метод получает список доступных в меню навигации типах модулей
     * включая возможные шаблоны
     *
     * @param bool $includeTemplates
     * @param bool $reinstall
     * @return array
     */
    public static function moduleList($includeTemplates = true, $reinstall = true)
    {
        $result = [];
        /**
         * @var $moduleManager ModuleManager
         */
        $moduleManager = \App::make('FastDog\Core\Module\ModuleManager');
        $modules = $moduleManager->getModules();
        /**
         * @var $module ModuleInterface
         */
        foreach ($modules as $module) {
            $info = $module->getModuleInfo($includeTemplates);
            if (is_array($info)) {
                foreach ($info as $item) {
                    if (isset($item['id'])) {
                        $result[$item['id']] = $item;
                    }
                }
            }
        }

        return $result;
    }

}