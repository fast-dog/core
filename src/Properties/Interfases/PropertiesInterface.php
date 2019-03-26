<?php

namespace FastDog\Core\Properties\Interfases;


use Illuminate\Support\Collection;

/**
 * Дополнительные параметры моделей
 *
 * @package FastDog\Core\Properties\Interfases
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface PropertiesInterface
{
    /**
     * Коллекция дополнительных параметров модели
     *
     * @return Collection
     */
    public function properties(): Collection;

    /**
     * Коллекция дополнительных параметров модели по умолчанию
     *
     * @return Collection
     */
    public function getDefaultProperties(): Collection;

    /**
     * Идентификатор модели
     *
     * @return int
     */
    public function getModelId(): int;
}