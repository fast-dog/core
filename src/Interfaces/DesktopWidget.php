<?php

namespace FastDog\Core\Interfaces;

/**
 * Блоки рабочего стола
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface DesktopWidget
{
    /**
     * Возвращает набор данных для отображения в блоке
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Устанавливает набор данных в контексте объекта
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data): void;


}