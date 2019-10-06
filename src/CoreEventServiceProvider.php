<?php

namespace FastDog\Core;

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
        'FastDog\Core\Events\GetComponentType' => [
            'FastDog\Core\Listeners\GetComponentType',// <-- Добавляем типы в список компонентов
        ],
        'FastDog\Core\Events\ItemReplicate' => [// <-- Копирование моделей
            'FastDog\Core\Listeners\ItemReplicate'
        ],
        'FastDog\Core\Events\FormBuilder' => [// <-- Настройка форм
            'FastDog\Core\Listeners\FormBuilder'
        ]
    ];

    /**
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
