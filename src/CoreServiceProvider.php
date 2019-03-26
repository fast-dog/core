<?php

namespace FastDog\Core;


use FastDog\Core\Models\DomainManager;
use FastDog\Core\Models\ModuleManager;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * Class CoreServiceProvider
 *
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class  CoreServiceProvider extends LaravelServiceProvider
{
    const NAME = 'core';

    /**
     * php composer.phar update fast_dog_core:dev-master --prefer-source
     *
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->handleConfigs();
        $this->handleRoutes();
        $this->handleMigrations();

        /**
         * Определяем параметры шаблона
         */
        DomainManager::initView();

        /**
         * Регистрация дериктив шаблонизатора в помощь верстальщикам
         */
        \Blade::directive('module', function ($name) {
            return "<?php echo FastDog\Core\\Module\\SiteModules::display($name); ?>";
        });

        \Blade::directive('field', function ($name, $view = '') {
            return "<?php echo FastDog\Modules\\Form\\Entity\\FormBase::display($name); ?>";
        });

        \Blade::directive('trans', function ($name, $view = '') {
            $trans_key = view()->shared('trans_key', 'public');

            return trans($trans_key . '.' . $name);
        });


        $this->handleViews();

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('ModuleManager', function () {
            $manager = new ModuleManager();
            $manager->getModules();

            return $manager;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Определение конфигурации по умолчанию
     */
    private function handleConfigs(): void
    {
        $configPath = __DIR__ . '/../config/core.php';
        $this->publishes([$configPath => config_path('core.php')]);

        $this->mergeConfigFrom($configPath, self::NAME);
    }

    /**
     * Миграции базы данных
     */
    private function handleMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations/');
    }

    /**
     * Определение представлении пакета (шаблонов по умолчанию)
     */
    private function handleViews(): void
    {
        $this->loadViewsFrom(__DIR__ . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR, self::NAME);

        $this->publishes([__DIR__ . DIRECTORY_SEPARATOR . '..' .
        DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR => base_path('resources/views/vendor/fast_dog/' . self::NAME)]);

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' => public_path('vendor/fast_dog'),
        ], 'public');
    }

    /**
     * Определение маршрутов пакета
     */
    private function handleRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
    }
}


