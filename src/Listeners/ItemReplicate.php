<?php

namespace FastDog\Core\Listeners;

use Illuminate\Http\Request;
use FastDog\Core\Events\ItemReplicate as ItemReplicateEvent;

/**
 * Class ItemReplicate
 * @package FastDog\Core\Listeners
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ItemReplicate
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
     * @param ItemReplicateEvent $event
     */
    public function handle(ItemReplicateEvent $event)
    {
        $this->request->merge([//<-- передаем клон в событие JsonPrepare, от туда на клиент
            '_replicate' => $event->getItem()->getData()
        ]);
    }
}
