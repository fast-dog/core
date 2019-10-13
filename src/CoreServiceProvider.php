<?php

namespace FastDog\Core;


use FastDog\Core\Models\Cache;
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
    /**
     * @const string
     */
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
        $this->handleLang();

        /**
         * Определяем параметры шаблона
         */
        DomainManager::initView();

        /**
         * Регистрация дериктив шаблонизатора в помощь верстальщикам
         */
        \Blade::directive('module', function($name) {
            return "<?php echo FastDog\Core\\Module\\SiteModules::display($name); ?>";
        });

        \Blade::directive('field', function($name, $view = '') {
            return "<?php echo FastDog\Modules\\Form\\Entity\\FormBase::display($name); ?>";
        });

        \Blade::directive('trans', function($name, $view = '') {
            $trans_key = view()->shared('trans_key', 'public');

            return trans($trans_key . '.' . $name);
        });


        $this->handleViews();

        $this->app->singleton(ModuleManager::class, function() {
            $manager = new ModuleManager();

            return $manager;
        });

        $this->app->singleton(Cache::class, function() {
            $cache = new Cache();

            return $cache;
        });
    }

    /**
     * Определение локализации
     */
    private function handleLang(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
        $this->loadTranslationsFrom($path, self::NAME);
        $this->publishes([
            $path => resource_path('lang/vendor/fast_dog/' . self::NAME),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(CoreEventServiceProvider::class);
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

//        $this->publishes([
//            realpath(__DIR__.'/../migrations/') => database_path('migrations'),
//        ], 'migrations');
    }

    /**
     * Определение представлении пакета (шаблонов по умолчанию)
     */
    private function handleViews(): void
    {
        $this->loadViewsFrom(__DIR__ . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR, self::NAME);

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR =>
                base_path('resources/views/vendor/fast_dog/'),
        ]);

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


