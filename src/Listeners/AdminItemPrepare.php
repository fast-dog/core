<?php

namespace FastDog\Core\Listeners;

use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Models\DomainManager;
use FastDog\Core\Models\ModuleManager;
use Illuminate\Http\Request;

/**
 * Class AdminPrepare
 * @package FastDog\Core\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AdminItemPrepare
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
    public function handle(AdminPrepareEventInterface $event)
    {
        /**
         * @var $moduleManager ModuleManager
         */
        $moduleManager = app()->make(ModuleManager::class);
        /** @var BaseModel $item */
        $item = $event->getItem();
        $data = $event->getData();
        $data['created_at'] = ($item->created_at !== null) ? $item->created_at->format('Y-m-d') : '';
        $data['updated_at'] = ($item->updated_at !== null) ? $item->updated_at->format('Y-m-d') : '';
        $data['published_at'] = ($item->published_at !== null) ? $item->published_at->format('Y-m-d') : '';
        $data['files_module'] = ($moduleManager->hasModule('FastDog\Modules\Media\Media')) ? 'Y' : 'N';

        if (is_string(['data'])) {
            $data ['data'] = json_decode($data['data']);
        }
        //Доступ
        if (isset($data[BaseModel::SITE_ID])) {
            $data[BaseModel::SITE_ID] = array_first(array_filter(DomainManager::getAccessDomainList(),
                function($element) use ($data) {
                    return $element['id'] == $data[BaseModel::SITE_ID];
                }));

        }

        //Состояние
        if (isset($data[BaseModel::STATE])) {
            $data[BaseModel::STATE] = array_first(array_filter(BaseModel::getStatusList(), function($element) use ($data) {
                return ($element['id'] == $data[BaseModel::STATE]);
            }));
        }

        //извлечение дополнительных параметров из json поля data для редактирования в форме
        if (method_exists($item, 'getExtractParameterNames')) {
            foreach ($item->getExtractParameterNames() as $id => $extractParameterName) {
                if (isset($data['data']->{$id})) {
                    $data[$id] = $data['data']->{$id};
                }
            }
        }


        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}