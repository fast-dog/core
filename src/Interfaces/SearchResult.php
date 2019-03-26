<?php

namespace FastDog\Core\Interfaces;

/**
 * Поддержка поиска
 *
 * Методы обеспечивающие модели поддержку модуля Поиск
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface SearchResult
{
    /**
     * Возвращает ссылку на результат поиска
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Возвращает название найденного объекта
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Возвращает текст
     *
     * @return string
     */
    public function getText(): string;

    /**
     * Возвращает дополнительные параметры найденного объекта
     *
     * @return mixed
     */
    public function getData(): \StdClass;
}