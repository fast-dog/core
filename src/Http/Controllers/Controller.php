<?php

namespace FastDog\Core\Http\Controllers;

use FastDog\Core\Events\JsonPrepare;
use FastDog\Core\Models\Domain;
use FastDog\Core\Models\DomainManager;
use FastDog\User\Models\MessageManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

/**
 * Базовый контроллер
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Кол-во элементов при постраничном выводе
     *
     * @const int
     */
    const PAGE_SIZE = 25;

    /**
     * Выполнять события при запросах
     *
     * @var bool $fireEvent
     */
    public $fireEvent = true;

    /**
     * Массив полей сортировки
     * @var array
     */
    public static $orderField = ['created_at', 'title'];

    /**
     * Направление сортировки
     *
     * @var array
     */
    public static $orderDirections = ['asc', 'desc'];

    /**
     * @var string $page_title
     */
    protected $page_title = '';

    /**
     * @var Collection $breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->breadcrumbs = collect([]);
        $this->breadcrumbs->push(['url' => '/', 'name' => trans('app.Главная')]);
    }

    /**
     * Ответ JSON
     *
     * Метод включает отладочную информацию и массив сообщений
     *
     * @param array $result
     * @param string $method
     * @return \Illuminate\Http\JsonResponse
     */
    public function json($result, $method = __METHOD__)
    {
        $result['token'] = csrf_token();

        if (config('app.debug')) {
            $result['_' . Domain::SITE_ID] = DomainManager::getSiteId();
            $result['_is_default'] = DomainManager::checkIsDefault();
            $result['_debug'] = config('app.debug');
            $result['__METHOD__'] = $method;
        }

        if (class_exists(MessageManager::class)) {
            /**
             * @var $messageManager MessageManager
             */
            $messageManager = \App::make(MessageManager::class);
            $result['messages'] = $messageManager->getNew();
        }

//        $result['notifications'] = Notifications::getNew();


        $result['page_title'] = $this->page_title;
        $result['breadcrumbs'] = $this->breadcrumbs;

        event(new JsonPrepare($result));

        return response()->json($result);
    }

    /**
     * Обновление полей модели
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     * @deprecated
     */
    protected function updatedModel($data, $model)
    {
        $updatedData = [];
        switch ($data['field']) {
            case 'published':
                $updatedData[$model::STATE] = ($data['value'] == 1) ? $model::STATE_PUBLISHED : $model::STATE_NOT_PUBLISHED;
                break;
            case 'trash':
                $updatedData[$model::STATE] = ($data['value'] == 1) ? $model::STATE_IN_TRASH : $model::STATE_NOT_PUBLISHED;
                break;
            case 'restored':
                $updatedData[$model::STATE] = $model::STATE_NOT_PUBLISHED;
                break;
            case 'deleted':
                if (isset($data['id']) && $data['id'] > 0) {
                    $model::where('id', $data['id'])->first()->delete();
                } elseif (isset($data['ids']) && count($data['ids'])) {
                    foreach ($data['ids'] as $id) {
                        $item = $model::where('id', $id)->first();
                        if ($item) {
                            $item->delete();
                        }
                    }
                }
                break;
            default:
                $updatedData[$data['field']] = $data['value'];
                break;
        }
        if (count($updatedData)) {
            if (isset($data['id']) && $data['id'] > 0) {
                if (is_string($model)) {
                    return $model::where('id', $data['id'])->update($updatedData);
                }

                return $model->where('id', $data['id'])->update($updatedData);
            } elseif (isset($data['ids']) && count($data['ids'])) {
                if (is_string($model)) {
                    return $model::whereIn('id', $data['ids'])->update($updatedData);
                }

                return $model->whereIn('id', $data['ids'])->update($updatedData);
            }
        }

        return false;
    }

    /**
     * Устанавливает текущие параметры постраницного вывода
     *
     * @param Request $request
     * @param LengthAwarePaginator $items
     * @param array $result
     * @return mixed
     * @deprecated
     */
    protected function _getCurrentPaginationInfo($request, $items, &$result)
    {
        $total = $items->total();
        $result['total'] = $total;
        $result['pages'] = ceil($total / $request->input('limit', self::PAGE_SIZE));
        $result['current_page'] = $request->input('page', 1);
        $offset = ($result['current_page'] == 1 ? 0 : ($result['current_page'] - 1) * self::PAGE_SIZE);
        $result['offset'] = $offset;

        return $request;
    }
}
