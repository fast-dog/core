<?php

namespace FastDog\Core\Interfaces;

use FastDog\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface AdminPrepareEventInterface
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface AdminPrepareEventInterface
{
    /**
     * Возвращает текущую модель
     *
     * @return Model
     */
    public function getItem(): Model;

    /**
     * Возвращает массив данных, содержимое зависит от контекста вызова события
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Устанавливает массив данных, содержимое зависит от контекста вызова события
     *
     * Обычно массив содержит детальные данные модели возвращаемые методом BaseModelInterface::getData()
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data): void;

    /**
     * Возвращает результирующий массив который будет передан на клиент в виде json объекта
     *
     * @return array
     */
    public function getResult(): array;

    /**
     * Устанавливает результирующий массив который будет передан на клиент в виде json объекта
     *
     * @param array $result
     */
    public function setResult(array $result): void;
}