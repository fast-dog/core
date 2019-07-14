<?php

namespace FastDog\Core;


use FastDog\Core\Policies\ComponentsPolicy;
use FastDog\User\Policies\UserPolicy;
use FastDog\User\Policies\UsersMailingPolicy;
use FastDog\User\Policies\UsersMailingTemplatesPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Class AuthServiceProvider
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Сопоставление политик для приложения.
     *
     * @var array
     */
    protected $policies = [
        \FastDog\Core\Models\Components::class => ComponentsPolicy::class,
    ];

    /**
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}