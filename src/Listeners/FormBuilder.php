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
class FormBuilder
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

        /**  @var $moduleManager ModuleManager */
        $moduleManager = \App::make(ModuleManager::class);
        /** @var BaseModel $item */
        $item = $event->getItem();
        $data = $event->getData();

        dd($data);

        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}