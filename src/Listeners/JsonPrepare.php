<?php

namespace FastDog\Core\Listeners;


use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use Illuminate\Http\Request;

/**
 * Class JsonPrepare
 * @package FastDog\Core\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class JsonPrepare
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
        $data = $event->getData();


        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }

        if ($this->request->has('_replicate')) {// <-- есть скопированный объект, передаем его на клиент
            $data['items'] = [];
            array_push($data['items'], $this->request->get('_replicate'));
        }
        $event->setData($data);
    }
}
