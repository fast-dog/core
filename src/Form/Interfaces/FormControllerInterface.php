<?php

namespace FastDog\Core\Form\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Формы в разделе администрирования
 *
 * Для реализации в контроллере
 *
 * @package FastDog\Core\Form\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface FormControllerInterface
{
    /**
     * Получает данные по запрошенной модели, метод устанавливает текущую
     * форму редактирования через слушатель события FastDog\Modules\[ModuleName]\Events\[ModelName]AdminPrepare
     *
     * @param Request $request
     * @return array
     */
    public function getItemData(Request $request): array;

    /**
     * Сохранение данных модели переданных POST с клиента
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postItem(Request $request): JsonResponse;

    /**
     * Событие обработки перед выводом данных на редактирование
     *
     * @return string
     */
    public function getPrepareEvent(): string;

    /**
     * Событие определения формы редактирования
     *
     * @return mixed
     */
    public function getSetFormEvent(): String;


    /**
     * Событие после сохранением
     *
     * @return mixed
     */
    public function getAfterSaveEvent(): String;

    /**
     * Событие перед сохранением
     *
     * @return mixed
     */
    public function getBeforeSaveEvent(): String;

    /**
     * Сохранение модели
     *
     * @return mixed
     */
    public function save(): bool;

    /**
     * Получение модели на редактирование
     *
     * @param $id
     * @return mixed
     */
    public function get($id): Model;

    /**
     * Получение модели
     *
     * @return Model
     */
    public function getModel(): Model;
}