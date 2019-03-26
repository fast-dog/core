<?php

namespace FastDog\Core;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class CoreEventServiceProvider
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class CoreEventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'FastDog\Core\Events\JsonPrepare' => [
            'FastDog\Core\Listeners\JsonPrepare',
        ],
    ];

    /**
     * @return void
     */
    public function boot()
    {
        parent::boot();


        //
    }

    public function register()
    {
        //
    }
}