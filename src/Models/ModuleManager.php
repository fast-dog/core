<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 01.11.2016
 * Time: 23:58
 */

namespace FastDog\Core\Models;


use FastDog\Core\Interfaces\ModuleInterface;
use FastDog\Core\Store;
use FastDog\Modules\Config\Entity\DomainManager;
use Chumper\Zipper\Zipper;

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
    public $moduleInstance = [];

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
     * Метод проверяет и устанавливает экземпляры модулей в контексте текущего объекта.
     *
     * Метод кэширует данные, если доступен Redis, кэширование будет в теге 'core'.
     * Ключ кэширования: __METHOD__ . '::' . DomainManager::getSiteId(). '::core-modules'
     *
     * @return array
     */
    public function getModules()
    {
        $key = __METHOD__ . '::' . DomainManager::getSiteId() . '::core-modules';
        $isRedis = config('cache.default') == 'redis';
        $this->moduleInstance = ($isRedis) ? \Cache::tags(['core'])->get($key, null) : \Cache::get($key, null);

        if ($this->moduleInstance === null) {
            $items = Module::get();
            $this->moduleInstance = [];
            foreach ($items as $item) {
                $item->{Module::DATA} = json_decode($item->{Module::DATA});
                if (isset($item->{Module::DATA}->source) && !isset($this->moduleInstance[$item->{Module::DATA}->source->class])) {
                    $this->moduleInstance[$item->{Module::DATA}->source->class] = new $item->{Module::DATA}->source->class();

                    $accessList = [];
                    if (isset($item->{Module::DATA}->route)) {
                        if (isset($item->{Module::DATA}->route->access)) {
                            $accessList[$item->{Module::DATA}->route->route] = $item->{Module::DATA}->route->access;
                        }
                        if (isset($item->{Module::DATA}->route->children)) {
                            foreach ($item->{Module::DATA}->route->children as $route) {
                                if (isset($route->access)) {
                                    $accessList[$route->route] = $route->access;
                                }
                            }
                        }
                    }
                    $item->{Module::DATA}->accessList = $accessList;
                    $this->moduleInstance[$item->{Module::DATA}->source->class]->setConfig($item->{Module::DATA});
                }
            }
            if ($isRedis) {
                \Cache::tags(['core'])->put($key, $this->moduleInstance, config('cache.tll_core', 5));
            } else {
                \Cache::put($key, $this->moduleInstance, config('cache.tll_core', 5));
            }
        }


        return $this->moduleInstance;
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

    /**
     * Переустановка модулей
     *
     * При переустановке модулей производится обнолвение списков доступа ACL и их сброс к состоянию по умолчанию.
     *
     * @param bool $reinstall переустановит модуль
     * @return void
     */
    public function reloadList($reinstall = true)
    {
        $dir = dirname(dirname(dirname(__FILE__) . '../') . '../') . '/Modules';
        $moduleDirs = \File::directories($dir);

        foreach ($moduleDirs as $moduleDir) {
            $file = $moduleDir . '/module.json';
            if (file_exists($file)) {
                $module = json_decode(file_get_contents($file));
                if ($module && $reinstall == true) {
                    $check = Module::where([
                        Module::NAME => $module->{Module::NAME},
                        Module::VERSION => $module->{Module::VERSION},
                    ])->first();
                    if (!$check) {
                        Module::create([
                            Module::NAME => $module->{Module::NAME},
                            Module::VERSION => $module->{Module::VERSION},
                            Module::PRIORITY => $module->{Module::PRIORITY},
                            Module::DATA => json_encode($module),
                        ]);
                    }
                    /**
                     * @var $mod  ModuleInterface
                     */
                    $mod = new $module->source->class();
                    $mod->initAcl();
                } else {
                    if (isset($module->source) && !isset($this->moduleInstance[$module->source->class])) {
                        $this->moduleInstance[$module->source->class] = new $module->source->class();
                    }
                }
            }
        }
    }

    /**
     * Директория tmp
     *
     * Возвращает путь к временной директории для резервного копирования\обновления модулей
     *
     * @return string
     */
    protected function getTmp()
    {
        return resource_path('modules');
//      return dirname(dirname(dirname(dirname(__FILE__) . '../') . '../') . '../') . DIRECTORY_SEPARATOR . '.tmp';
    }

    /**
     * Директория модулей
     *
     * @return string
     */
    protected function getModulesDir()
    {
        return dirname(dirname(dirname(__FILE__) . '../') . '../') . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR;
    }

    /**
     * Директория ресурсов
     *
     * Возвращает дирекотрю шаблонов\файлов локализации
     *
     * @return string
     */
    protected function getResourceDir()
    {
        return dirname(dirname(dirname(dirname(__FILE__) . '../') . '../') . '../') . DIRECTORY_SEPARATOR;//. 'resources' . DIRECTORY_SEPARATOR;
    }

    /**
     * Выполнить команду
     *
     * Выполнение операций обновления, резервного копирования модулей.
     *
     * Метод в стадии разработки.
     *
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public function cmd($params = [])
    {
        $tmp = $this->getTmp();
        $params['id'] = studly_case($params['id']);

        if (is_dir($tmp)) {
            switch ($params['action']) {
                case 'backup':
                    return $this->moduleBackup($params);
                case 'update':
                    $oldModuleDir = $this->getModulesDir() . $params['id'];

                    if (is_dir($oldModuleDir)) {
                        $moduleOldConfig = json_decode(file_get_contents($oldModuleDir . DIRECTORY_SEPARATOR . 'module.json'));

                        $zipper = new  Zipper;
                        $module = $tmp . DIRECTORY_SEPARATOR . 'new' . DIRECTORY_SEPARATOR . $params['id'] . '.zip';

                        if (file_exists($module)) {
                            $zipper->make($module);
                            $moduleJson = $zipper->listFiles('/\module.json/i');

                            if (count($moduleJson) >= 1) {
                                $moduleNewConfig = json_decode($zipper->getFileContent($moduleJson[0]));

                                if ($moduleOldConfig->version !== $moduleNewConfig->version) {
                                    $this->moduleBackup($params);

                                    $zipper->extractTo($this->getModulesDir(), [$params['id']], Zipper::WHITELIST);
                                    $zipper->extractTo($this->getResourceDir(), ['resources'], Zipper::WHITELIST);
                                    $moduleSqlUpdate = $zipper->listFiles('/\update.sql/i');

                                }
                            }
                            $zipper->close();
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Резервное копирование модуля
     *
     * Выполняется резервное копирование файлов и таблиц базы данных модуля, для копирования таблиц они должны быть
     * перечислены в module.json
     *
     *
     * Метод в стадии разработки.
     *
     * @param $params
     * @return bool
     * @throws \Spatie\DbDumper\Exceptions\CannotSetParameter
     * @throws \Spatie\DbDumper\Exceptions\CannotStartDump
     * @throws \Spatie\DbDumper\Exceptions\DumpFailed
     */
    protected function moduleBackup($params): bool
    {
        $tmp = $this->getTmp();
        $modulesDir = $this->getModulesDir();

        $oldModuleDir = $modulesDir . studly_case($params['id']);

        if (is_dir($oldModuleDir)) {
            $moduleOldConfig = json_decode(file_get_contents($oldModuleDir . DIRECTORY_SEPARATOR . 'module.json'));
            $modules = $this->getModules();
            /** @var ModuleInterface $module */
            $module = $modules[$moduleOldConfig->source->class];
            /** @var array $tables */
            $tables = $module->getTables();

            if (count($tables)) {//<-- Если есть таблицы для копирования
                /**
                 * Создаем резервную копию базы данных
                 */
                $bdDumper = \Spatie\DbDumper\Databases\MySql::create();

                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    //  Путь к Mysql
                    $bdDumper->setDumpBinaryPath('C:\OSPanel\modules\database\MySQL-5.7-x64\bin');
                }

                $bdDumper
                    ->setDbName(config('database.connections.mysql.database'))
                    ->setUserName(config('database.connections.mysql.username'))
                    ->setPassword(config('database.connections.mysql.password'))
                    ->includeTables($tables)
                    ->dumpToFile($tmp . DIRECTORY_SEPARATOR . 'old' . DIRECTORY_SEPARATOR . 'dump.sql');
            }


            $backUpZipper = new  Zipper;
            $backUpName = $tmp . DIRECTORY_SEPARATOR . 'old' . DIRECTORY_SEPARATOR . 'backup_' . $params['id'] . '_' . $moduleOldConfig->version . '.zip';

            /**
             * Архивируем файлы старого модуля
             */
            $backUpZipper->make($backUpName)->add($oldModuleDir . DIRECTORY_SEPARATOR);

            //добавляем в архив резервную копию базы данных
            if (file_exists($tmp . DIRECTORY_SEPARATOR . 'old' . DIRECTORY_SEPARATOR . 'dump.sql')) {
                $backUpZipper->add($tmp . DIRECTORY_SEPARATOR . 'old' . DIRECTORY_SEPARATOR . 'dump.sql');
            }

            //закрываем архив
            $backUpZipper->close();

            //удаляем резервную копию базы данных
            if (file_exists($tmp . DIRECTORY_SEPARATOR . 'old' . DIRECTORY_SEPARATOR . 'dump.sql')) {
                unlink($tmp . DIRECTORY_SEPARATOR . 'old' . DIRECTORY_SEPARATOR . 'dump.sql');
            }
        }

        return true;
    }

    public function moduleInstall($params)
    {
        $tmp = $this->getTmp();
        $modulesDir = $this->getModulesDir();

        $oldModuleDir = $modulesDir . $params['id'];
        if (is_dir($oldModuleDir)) {
            $moduleOldConfig = json_decode(file_get_contents($oldModuleDir . DIRECTORY_SEPARATOR . 'module.json'));

        }
    }
}