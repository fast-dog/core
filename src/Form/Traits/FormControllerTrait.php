<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 05.04.2018
 * Time: 8:41
 */

namespace FastDog\Core\Form\Traits;

use FastDog\Core\Models\BaseModel;
use FastDog\Core\Properties\BaseProperties;
use FastDog\Core\Properties\BasePropertiesSelectValues;
use FastDog\Core\Properties\Traits\PropertiesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Trait FormControllerTrait
 * @package FastDog\Core\Form\Traits
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
trait FormControllerTrait
{
    /**
     * Текущаяя модель
     *
     * @var BaseModel|null $model
     */
    protected $model;

    /**
     * Текущаяя загруженная модель
     *
     * @var BaseModel|null $item
     */
    protected $item;

    /**
     * Возвращает данные для редактирования, вызывается событие
     * обработки данных модели FastDog\Modules\[ModuleName]\Events\[ModelName]AdminPrepare
     *
     * @param Request $request
     * @return array
     */
    public function getItemData(Request $request): array
    {
        $result = [
            'success' => true,
            'items' => [],
        ];

        $this->get($request->input('id', \Route::input('id')));

        if ($this->item) {
            $data = $this->item->getData();

            $eventPrepare = $this->getPrepareEvent();

            if ($eventPrepare && class_exists($eventPrepare)) {
                event(new $eventPrepare($data, $this->item, $result));
            }
            array_push($result['items'], $data);
        }


        return $result;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postItem(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    /**
     * Событие обработки перед выводом данных на редактирование
     *
     * @return string
     */
    public function getPrepareEvent(): string
    {
        return $this->model->getEventAdminPrepareName();
    }

    /**
     * Событие определения формы редактирования
     *
     * @return string
     */
    public function getSetFormEvent(): string
    {
        return '';
    }

    /**
     * Событие после сохранением
     * @return string
     */
    public function getAfterSaveEvent(): string
    {
        return '';
    }

    /**
     * Событие перед сохранением
     * @return string
     */
    public function getBeforeSaveEvent(): string
    {
        return '';
    }

    /**
     * Сохранение модели
     * @return mixed
     */
    public function save(): bool
    {
        return false;
    }

    /**
     * Получение модели на редактирование
     *
     * @param $id
     * @return Model
     */
    public function get($id): Model
    {
        if ($id == 0) {
            $item = $this->getModel();
        } else {
            /** @var BaseModel $item */
            $item = $this->getModel()->find($id);
        }
        $this->setItem($item);

        return $item;
    }

    /**
     * Определение редактируемой модели
     *
     * @param $item Model
     */
    public function setItem(Model $item): void
    {
        $this->item = $item;
    }

    /**
     * Получение редактируемой модели
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Удаление варианта значения дополнительного параметра
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deletePropertySelectValue(Request $request): JsonResponse
    {
        $result = [
            'success' => true,
        ];
        switch ($request->input(BaseProperties::TYPE, null)) {
            case BaseProperties::TYPE_SELECT:
                BasePropertiesSelectValues::where([
                    'id' => $request->id,
                    BasePropertiesSelectValues::PROPERTY_ID => $request->input(BasePropertiesSelectValues::PROPERTY_ID),
                ])->delete();
                break;
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Добавление варианта значения дополнительного параметра
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addPropertySelectValue(Request $request): JsonResponse
    {
        $result = [
            'success' => true,
            'items' => [],
        ];
        switch ($request->input(BaseProperties::TYPE . '.id', null)) {
            case BaseProperties::TYPE_SELECT:
                $data = [
                    BasePropertiesSelectValues::ALIAS => $request->input(BasePropertiesSelectValues::ALIAS),
                    BasePropertiesSelectValues::NAME => $request->input(BasePropertiesSelectValues::NAME),
                    BasePropertiesSelectValues::PROPERTY_ID => $request->input(BasePropertiesSelectValues::PROPERTY_ID),
                ];
                $id = (int)$request->input('id');
                if ($id == 0) {
                    BasePropertiesSelectValues::create($data);
                } else {
                    BasePropertiesSelectValues::where('id', $id)->update($data);
                }

                BasePropertiesSelectValues::where([
                    BasePropertiesSelectValues::PROPERTY_ID => $request->input(BasePropertiesSelectValues::PROPERTY_ID),
                ])->get()->each(function (BasePropertiesSelectValues $item) use (&$result) {
                    array_push($result['items'], $item->getData());
                });
                break;
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveProperty(Request $request): JsonResponse
    {
        $result = [
            'success' => false,
            'items' => [],
            'item' => null,
        ];
        /** @var $model BaseModel|PropertiesTrait */
        $model = $this->getModel();
        if ($modelName = $request->input('model_class', null)) {
            if (class_exists($modelName)) {
                $model = new $modelName();
            }
        }

        if ($model->getTable() !== '') {
            $model = $model->find($request->input('item_id'));
        }

        $id = (int)$request->input('id');

        $data = [
            BaseProperties::NAME => $request->input(BaseProperties::NAME),
            BaseProperties::ALIAS => $request->input(BaseProperties::ALIAS),
            BaseProperties::TYPE => $request->input(BaseProperties::TYPE . '.id'),
            BaseProperties::SORT => (int)$request->input(BaseProperties::SORT),
            BaseProperties::MODEL => (int)$request->input('model_id'),
            BaseProperties::DATA => json_encode([
                'description' => $request->input('data.description'),
            ]),
        ];

        $check = BaseProperties::where([
            BaseProperties::ALIAS => $data[BaseProperties::ALIAS],
            BaseProperties::MODEL => (int)$request->input('model_id'),
        ])->first();
        if ($check) {
            $id = $check->id;
        }

        /** @var $item BaseProperties */
        if ($id == 0) {
            $item = BaseProperties::create($data);
        } else {
            BaseProperties::where([
                'id' => $id,
            ])->update($data);

            $item = BaseProperties::find($id);
        }
        $result['items'] = ($model) ? $model->properties() : collect([]);
        if ($item) {
            $result['success'] = true;
            $result['items']->each(function ($property) use (&$result, $id, $model) {
                if ($property['id'] == $id) {
                    $property['model_id'] = $model->getModelId();
                    $result['item'] = $property;
                }
            });
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Обновление основных параметров модели из таблицы
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postModelUpdateFromTable(Request $request): JsonResponse
    {
        $result = ['success' => false, 'items' => []];
        if (method_exists($this, 'updatedModel')) {
            $this->updatedModel($request->all(), $this->getModel());
            $result['success'] = true;
        }

        return $this->json($result, __METHOD__);
    }
}