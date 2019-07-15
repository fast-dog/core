<?php

namespace FastDog\Core\Listeners;



use FastDog\Core\Models\Components;
use Illuminate\Http\Request;
use FastDog\Core\Events\GetComponentType as GetComponentTypeEvent;

/**
 * Class GetComponentType
 * @package FastDog\Core\Listeners
 * @version 0.1.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class GetComponentType
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
     * @param GetComponentTypeEvent $event
     */
    public function handle(GetComponentTypeEvent $event)
    {
        $data = $event->getData();

        $paths = array_first(\Config::get('view.paths'));
        array_push($data, [
            'id' => 'core',
            'instance' => Components::class,
            'name' => trans('core::components.default'),
            'items' => [
                [
                    'id' => 'html',
                    'name' => trans('core::components.default') . ' :: ' . trans('core::components.html'),
                    'templates' => Components::getTemplates($paths . '/vendor/fast_dog/core/components/html/*.blade.php'),
                ],
                [
                    'id' => 'breadcrumbs',
                    'name' => trans('core::components.default') . ' :: ' . trans('core::components.breadcrumbs'),
                    'templates' => Components::getTemplates($paths . '/vendor/fast_dog/core/components/breadcrumbs/*.blade.php'),
                ],
                [
                    'id' => 'language',
                    'name' => trans('core::components.default') . ' :: ' . trans('core::components.language'),
                    'templates' => Components::getTemplates($paths . '/vendor/fast_dog/core/components/language/*.blade.php'),
                ],
            ],
        ]);


        if (config('app.debug')) {
            $data['_events'][] = __METHOD__;
        }
        $event->setData($data);
    }
}