<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 16.03.2018
 * Time: 21:30
 */

namespace FastDog\Core\Table\Traits;

use FastDog\Core\Models\BaseModel;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Table\BaseTable;
use FastDog\Core\Table\Filters\BaseFilter;
use FastDog\Core\Table\Interfaces\TableModelInterface;
use Baum\Node;
use Carbon\Carbon;
use FastDog\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Таблицы раздела администрирования
 *
 * @package FastDog\Core\Table\Traits
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
trait TableTrait
{
    /**
     * Кол-во записей на страницу
     * @var int $limit
     */
    protected $limit = 25;

    /**
     * Поле сортировки по умолчанию
     * @var string $order
     */
    protected $order = 'id';

    /**
     * Направление сортировки по умолчанию
     * @var string $direction
     */
    protected $direction = 'desc';

    /**
     * Формат даты по умолчанию
     * @var string $defaultDateTimeFormat
     */
    protected $defaultDateTimeFormat = 'd.m.Y H:i';

    /**
     * @var null|BaseTable $table
     */
    protected $table = null;

    /**
     * Режим выборки list|tree
     * @var string $mode
     */
    protected $mode = 'list';

    /**
     * Базовая модель для отображения данных
     *
     * @return TableModelInterface
     */
    public function getModel(): TableModelInterface
    {
        return null;
    }

    /**
     * @param string $mode
     */
    protected function initTable($mode = 'list')
    {
        $this->table = BaseTable::where([
            BaseTable::NAME => class_basename($this->getModel()),
        ])->first();
        $this->mode = $mode;
        if ($this->table === null) {
            $this->table = BaseTable::create([
                BaseTable::NAME => class_basename($this->model),
                BaseTable::DATA => json_encode([
                    'cols' => $this->getModel()->getTableCols(),
                ]),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function paginate(Request $request): array
    {
        /**
         * @var $cols Collection
         */
        $cols = $this->getCols();

        $result = [
            'success' => true,
            'items' => [],
            'cols' => $cols,
            'access' => $this->getAccess(),
            'filters' => $this->getFilters(),
        ];

        switch ($this->mode) {
            case 'list':
                $selectField = $this->getSelectField($cols);
                /**
                 * @var $items Collection
                 */
                $items = $this->getModel()
                    ->select($selectField)
                    ->where(function (Builder $query) {
                        $this->setFilters($query);
                        if (method_exists($this->model, 'setFilters')) {
                            $this->model->setFilters($query);
                        }
                    })
                    ->orderBy($this->order, $this->direction)
                    ->paginate(\Request::input('limit', $this->limit));
                $items->each(function ($item) use ($cols, &$result, $selectField) {
                    $data = [];
                    if (isset($item->{BaseModel::STATE})) {
                        $data[BaseModel::STATE] = $item->{BaseModel::STATE};
                    }
                    if (in_array(BaseModel::DATA, $selectField)) {
                        $data[BaseModel::DATA] = (is_string($item->{BaseModel::DATA})) ? json_decode($item->{BaseModel::DATA}) : $item->{BaseModel::DATA};
                    }

                    $cols->each(function ($col) use ($item, &$data, &$relations) {
                        $col = (array)$col;
                        if (isset($col['related'])) {
                            $related = explode(':', $col['related']);
                            if (!isset($relations[$col['key']][$item->{$col['key']}])) {
                                $relations[$col['key']][$item->{$col['key']}] = $item->{$related[0]};
                            }
                            if ($relations[$col['key']][$item->{$col['key']}]) {
                                switch ($related[1]) {
                                    case 'count()':
                                        $data[$col['key']] = $relations[$col['key']][$item->{$col['key']}]->count();
                                        break;
                                    default:
                                        $data[$col['key']] = $relations[$col['key']][$item->{$col['key']}]->{$related[1]};
                                        break;
                                }
                            }
                        } else {
                            if ($item->{$col['key']} instanceof Carbon) {
                                $data[$col['key']] = $item->{$col['key']}->format($this->getDateTimeFormat());
                            } else {
                                $data[$col['key']] = $item->{$col['key']};
                            }
                        }
                    });
                    $data['checked'] = false;

                    if (isset($item->{BaseModel::SITE_ID})) {
                        $data['suffix'] = DomainManager::getDomainSuffix($item->{BaseModel::SITE_ID});
                    }
                    foreach ($selectField as $key) {

                        if (!isset($data[$key]) && isset($item->{$key})) {
                            $data[$key] = $item->{$key};
                        }
                    }
                    array_push($result['items'], $data);
                });

                $this->_getCurrentPaginationInfo($request, $items, $result);
                break;
            case 'tree':
                $view = $request->input('view', 'list');
                /**
                 * @var $items Collection
                 */
                $items = $this->getModel()->where(function (Builder $query) {
                    $query->where('lft', 1);
                    if (!DomainManager::checkIsDefault()) {
                        $query->where(BaseModel::SITE_ID, DomainManager::getSiteId());
                    }
                })->get();
                switch ($view) {
                    case 'tree':
                        $items->toHierarchy();
                        break;
                }
                $items->each(function (Node $item) use (&$result, $view) {
                    $data = $item->getData();
                    if (DomainManager::checkIsDefault()) {
                        $data['suffix'] = DomainManager::getDomainSuffix($data[BaseModel::SITE_ID]);
                    }

                    switch ($view) {
                        case 'tree':

                            $data['children'] = [];

                            $this->getChildren($item, $data);

                            array_push($result['items'], $data);
                            break;
                        default:
                            array_push($result['items'], $data);
                            $item->descendants()
                                ->where(function (Builder $query) use (&$result) {
                                    $this->setFilters($query);
                                })
                                ->get()
                                ->each(function (Node $item) use (&$result) {
                                    $data = $item->getData();
                                    if (DomainManager::checkIsDefault()) {
                                        $data['suffix'] = DomainManager::getDomainSuffix($data[BaseModel::SITE_ID]);
                                    }
                                    array_push($result['items'], $data);
                                });
                            break;
                    }
                });

                break;
        }


        return $result;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    protected function setFilters(Builder $query): Builder
    {
        $filters = \Request::input('filters', []);
        $prepared = [];
        foreach ($filters as $filter) {
            $filter = (object)$filter;
            if (isset($filter->type)) {
                if (isset($filter->values)) {
                    $filter->value = $filter->values;
                }
                switch ($filter->type) {
                    case BaseFilter::TYPE_TEXT:
                    case BaseFilter::TYPE_SELECT:
                    case BaseFilter::TYPE_DATETIME:
                        switch ($filter->operator['value']) {
                            case '=':
                            case '!=':
                                $prepared[$filter->name] = $filter->name . ' ' . $filter->operator['value'] . " '" .
                                    $filter->value . "'";
                                break;
                            case 'IN':
                            case 'NOT IN':
                                $prepared[$filter->name] = $filter->name . ' ' . $filter->operator['value'] .
                                    " (" . $filter->value . ")";
                                break;
                            case 'BETWEEN':
                                $prepared[$filter->name] = " (" . $filter->name . ' ' . $filter->operator['value'] .
                                    "'" . $filter->value[0] . "' AND '" . $filter->value[1] . "')";
                                break;
                            case 'LIKE':
                                $prepared[$filter->name] = $filter->name . ' ' . $filter->operator['value'] . " '%" .
                                    $filter->value . "%'";
                                break;
                        }
                        break;
                    case  BaseFilter::TYPE_OPERATOR:
                        $prepared[] = $filter->value;
                        break;
                }
            }
        }

        if (count($prepared)) {
            $query->whereRaw(\DB::raw(implode(' ', $prepared)));
        }

        return $query;
    }

    /**
     * @param Collection $cols
     * @return array
     */
    protected function getSelectField(Collection $cols): array
    {
        $result = $this->getDefaultSelectFields();
        $cols->each(function ($col) use (&$result) {
            if (!in_array($col->key, ['#'])) {
                array_push($result, $col->key);
            }
        });

        return $result;
    }

    /**
     * Поля для выборки по умолчанию
     *
     * @return array
     */
    public function getDefaultSelectFields(): array
    {
        return [BaseModel::STATE, BaseModel::DELETED_AT];
    }

    /**
     * Форматирование полуй даты времени по умолчанию
     *
     * @return String
     */
    protected function getDateTimeFormat(): String
    {
        return $this->defaultDateTimeFormat;
    }

    /**
     * Парамтеры доступа к методам интерфейса
     *
     * @return array
     */
    protected function getAccess(): array
    {
        /**
         * @var $user User
         */
        $user = \Auth::getUser();

        return [
            'reorder' => ($user) ? $user->can('reorder') : false,
            'delete' => ($user) ? $user->can('delete') : false,
            'update' => ($user) ? $user->can('update') : false,
            'create' => ($user) ? $user->can('create') : false,
        ];
    }

    /**
     * Устанавливает текущие параметры постраницного вывода
     *
     * @param Request $request
     * @param LengthAwarePaginator|Collection $items
     * @param array $result
     * @return mixed
     */
    protected function _getCurrentPaginationInfo($request, $items, &$result)
    {
        $total = $items->total();
        $result['total'] = $total;
        $result['pages'] = ceil($total / $request->input('limit', $this->limit));
        $result['current_page'] = $request->input('page', 1);
        $offset = ($result['current_page'] == 1 ? 0 : ($result['current_page'] - 1) * $this->limit);
        $result['offset'] = $offset;

        return $request;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        $result = $this->getModel()->getAdminFilters();

        return $result;
    }

    /**
     * Сортировка списка
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        $searchIdx = null;
        /**
         * @var $node Node
         */
        $node = $this->model->where('id', $request->input('item_id', 0))->first();
        if ($node) {
            $list = $request->input('items', []);
            $searchIdx = array_search($node->id, $list);
            if ($searchIdx > 0 && isset($list[$searchIdx - 1])) {
                $prevNode = $this->model->find($list[$searchIdx - 1]);
                if ($prevNode->depth == $node->depth) {
                    $node->moveToRightOf($prevNode);
                    $node->setDepth();

                    return $this->json([
                        'success' => true,
                        'parent' => $prevNode->id,
                        'method' => 'moveToRightOf',
                    ], __METHOD__);
                } else if (($prevNode->depth + 1) == $node->depth) {
                    $node->makeFirstChildOf($prevNode);
                    $node->setDepth();

                    return $this->json(['success' => true, 'parent' => $prevNode->id, 'method' => 'makeFirstChildOf'],
                        __METHOD__);
                } else {// Ошибка вложенности, берем следующий элемент
                    if (isset($list[$searchIdx + 1])) {
                        $nextNode = $this->model->find($list[$searchIdx + 1]);
                        if ($nextNode->depth == $node->depth) {
                            $node->moveToLeftOf($nextNode);
                            $node->setDepth();

                            return $this->json([
                                'success' => true,
                                'parent' => $prevNode->id,
                                'method' => 'moveToLeftOf',
                            ], __METHOD__);
                        }
                    }

                    return $this->json([
                        'success' => false,
                        '$prevNode->depth' => $prevNode->depth,
                        '$node->depth' => $node->depth,
                        'message' => 'Ошибка вложенности',
                    ], __METHOD__);
                }
            } else if ($searchIdx == 0) {
                $prevNode = $this->model->find($list[1]);
                if ($prevNode->depth == $node->depth) {
                    $node->moveToLeftOf($prevNode);
                    $node->setDepth();

                    return $this->json([
                        'success' => true,
                        'parent' => $prevNode->id,
                        'method' => 'moveToLeftOf',
                    ], __METHOD__);
                }
            } else {
                return $this->json([
                    'success' => false,
                    '$searchIdx' => $searchIdx,
                    'message' => 'Ошибка перемещения',
                ], __METHOD__);
            }
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Объект не найден в базе данных',
            ], __METHOD__);
        }

        return $this->json([
            'success' => false,
            '$searchIdx' => $searchIdx,
            'message' => 'Объект не перемещен',
        ], __METHOD__);
    }

    /**
     * Сортировка дерева
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorderTree(Request $request): JsonResponse
    {
        $result = ['success' => true];

        /**
         * @var $node Menu
         */
        $node = $this->model->find($request->input('id'));
        /**
         * @var $parent Menu
         */
        $parent = $this->model->find($request->input('parent'));

        if ($node === null || $parent === null) {
            return $this->json([
                'success' => false,
                'message' => 'Невозможно выполнить команду',
            ], __METHOD__);
        }
        if ($node->{$this->model::SITE_ID} !== $parent->{$this->model::SITE_ID}) {
            return $this->json([
                'success' => false,
                'message' => 'Изменить сайт привязки нельзя.',
            ], __METHOD__);
        }
        if ($node && $parent) {
            $oldPosition = [];
            $children = $parent->descendants()->limitDepth(1)->get();
            if ($children) {
                foreach ($children as $child) {
                    array_push($oldPosition, $child->id);
                }
                $position = ($request->input('position'));
                switch ($request->input('move')) {
                    case 'up':
                        if (isset($oldPosition[$position])) {
                            $prevId = $oldPosition[$position];
                            if ($prevId == $node->id) {
                                $prevId = $oldPosition[$position];
                            }
                            $prevNode = $this->model->find($prevId);
                            $node->moveToLeftOf($prevNode);
                            $node->setDepth();
                            $result = [
                                'success' => true, 'prev' => $prevNode->id,
                                'method' => 'moveToLeftOf'];
                        }
                        break;
                    case 'down':
                        if (isset($oldPosition[$position])) {
                            $prevId = $oldPosition[$position];
                            if ($prevId == $node->id) {
                                $prevId = $oldPosition[$position];
                            }
                            $prevNode = $this->model->find($prevId);
                            $node->moveToRightOf($prevNode);
                            $node->setDepth();
                            $result = [
                                'success' => true, 'prev' => $prevNode->id,
                                'method' => 'moveToRightOf',
                            ];
                        }
                        break;
                    case 'insert':
                        $parentNode = $this->model->find($request->input('parent'));
                        if ($parentNode) {
                            $node->makeLastChildOf($parentNode);
                            $node->setDepth();
                            $result = [
                                'success' => true, 'parent' => $parentNode->id,
                                'method' => 'makeLastChildOf',
                            ];
                        }
                        break;
                }
            }
        }

        return $this->json($result, __METHOD__);
    }

    /**
     * Удаление позиции каталога
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function itemDelete(Request $request): JsonResponse
    {
        $result = ['success' => true, 'items' => []];

        $this->model->whereIn('id', $request->input('ids', []))->delete();

        return $this->json($result, __METHOD__);
    }
}