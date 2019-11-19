<?php

namespace FastDog\Core\Listeners;

use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Core\Models\BaseModel;
use Illuminate\Http\Request;

/**
 * Упаковывает данные с отдельных полей в json объект data,
 * в модели должен быть определен метод getExtractParameterNames
 *
 *
 * @package FastDog\Core\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ModelBeforeSave
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * ContentAdminPrepare constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param AdminPrepareEventInterface $event
     */
    public function handle($event)
    {
        /**
         * @var $model BaseModel
         */
        $model = $event->getItem();
        $data = $event->getData();
        $data['data'] = (is_string($data['data'])) ? (object)json_decode($data['data']) : (object)$data['data'];

        $packParameters = $model->getExtractParameterNames();
        $allData = $this->request->all();
        foreach ($packParameters as $name) {
            if (isset($allData[$name])) {
                $data['data']->{$name} = $allData[$name];
            }
        }
        $data['data'] = json_encode($data['data']);
        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}