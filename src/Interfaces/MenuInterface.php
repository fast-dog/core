<?php

namespace FastDog\Core\Interfaces;

/**
 * Меню навигации
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface MenuInterface
{
    /**
     * Возвращает массив метаданных включая open Graph
     *
     * @return array
     */
    public function getMetadata(): array;

    /**
     * Определение типа меню
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Типы меню в проекте
     *
     * @return array
     */
//    public static function getTypes(): array;

    /**
     * Http ссылка
     *
     * @param bool $url
     * @return string
     */
    public function getUrl($url = true): string;


    /**
     * Маршрут компонента
     *
     * @return string
     */
    public function getRoute(): string;

    /**
     * Имя пункта меню
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Цепочка навигации
     *
     * @return array
     */
    public function getPath(): array;
}
