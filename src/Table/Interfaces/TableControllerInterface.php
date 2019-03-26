<?php

namespace FastDog\Core\Table\Interfaces;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Таблица в разделе администрирования
 *
 * Для реализации в контроллерах
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface TableControllerInterface
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function items(Request $request): JsonResponse;

    /**
     * Модель, контекст выборок
     *
     * @return  Model
     */
    public function getModel();

    /**
     * Описание структуры колонок таблицы
     *
     * @return Collection
     */
    public function getCols(): Collection;

}