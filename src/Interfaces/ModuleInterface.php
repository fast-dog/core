<?php

namespace FastDog\Core\Interfaces;

use FastDog\Core\Models\Components;
use Illuminate\Http\Request;

/**
 * Описание основных методов модулей
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface ModuleInterface
{
    /**
     * Устанавливает параметры в контексте объекта
     *
     * @param \StdClass $data
     * @return void
     */
    public function setConfig(\StdClass $data): void;

    /**
     * Возвращает параметры объекта
     *
     * @return \StdClass
     */
    public function getConfig(): \StdClass;


    /**
     * События обрабатываемые модулем
     *
     * @return void
     */
    public function initEvents(): void;

    /**
     * Возвращает доступные шаблоны для отображения страниц
     *
     * @param $paths
     * @return array
     */
    public function getTemplates($paths = ''): array;

    /**
     * Возвращает доступные типы меню предоставляемые модулем
     *
     * @return array
     */
    public function getMenuType(): array;

    /**
     * Возвращает детальную информацию о модуле
     *
     * @param bool $includeTemplates
     * @return array
     */
    public function getModuleInfo($includeTemplates = true): array;


    /**
     * Возвращает возможные типы модулей подключаемых в страницах
     *
     * @return mixed
     */
    public function getModuleType(): array;

    /**
     * Возвращает маршрут компонента
     *
     * @param Request $request
     * @param MenuInterface $item
     * @return array
     */
    public function getMenuRoute(Request $request, MenuInterface $item): array;

    /**
     * Метод возвращает отображаемый в публичной части контнет
     *
     * @param Components $module
     * @return string
     */
    public function getContent(Components $module): string;

    /**
     * Метод возвращает директорию модуля
     *
     * @return string
     */
    public function getModuleDir(): string;

    /**
     * Возвращает параметры блоков добавляемых на рабочий стол администратора
     *
     * @return array
     */
    public function getDesktopWidget(): array;

    /**
     * Возвращает массив таблиц для резервного копирования
     *
     * @return array
     */
    public function getTables(): array;
}