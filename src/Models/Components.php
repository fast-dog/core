<?php namespace FastDog\Core\Module;


use FastDog\Core\Media\Interfaces\MediaInterface;
use FastDog\Core\Media\Traits\MediaTraits;
use FastDog\Core\Models\BaseModel;
use FastDog\Core\Properties\Interfases\PropertiesInterface;
use FastDog\Core\Properties\Traits\PropertiesTrait;
use FastDog\Core\Table\Interfaces\TableModelInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Реализация контейнеров HTML (модулей)
 *
 *
 * Реализация контейнеров выводящих HTML разметку в публичной части сайта, в зависимости от настраиваемых параметров.
 * Простейший пример использования в шаблонах blade:
 * <pre>
 *      &lt;div class=&quot;collapse navbar-collapse mega-menu navbar-responsive-collapse&quot;&gt;
 *          &lt;div class=&quot;container&quot;&gt;
 *              &#64;module('Верхнее меню')
 *          &lt;/div&gt;&lt;!--/end container--&gt;
 *      &lt;/div&gt;&lt;!--/navbar-collapse--&gt;
 * </pre>
 *
 * @package FastDog\Core\Module
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Components extends BaseModel implements TableModelInterface, PropertiesInterface, MediaInterface
{
    use PropertiesTrait, MediaTraits;

    /**
     * Название таблицы
     * @var string $table
     */
    protected $table = 'system_site_modules';

    /**
     * Массив полей автозаполнения
     *
     * @var array $fillable [self::NAME, self::DATA, self::STATE]
     */
    protected $fillable = [self::NAME, self::DATA, self::STATE, self::SITE_ID];

    /**
     * Установленные модули
     *
     * Массив установленных модулей в виде ключ (состанвное значение) => имя объекта для получения контента
     *
     * <pre>
     *  [
     *    "core::html" => "FastDog\Core\Module\SiteModules"
     *    "banner::items" => "App\Modules\Banner\Banners"
     *  ]
     *</pre>
     *
     * @var array $modules
     */
    public static $modules = [];

    /**
     * Возвращает данные объекта
     *
     * @return array
     */
    public function getData(): array
    {
        $_data = json_decode($this->{self::DATA});
        if (empty($_data)) {
            $_data = [];
        }
        $data = [
            'id' => $this->id,
            self::NAME => $this->{self::NAME},
            self::STATE => ($this->{self::STATE} === null) ? self::STATE_PUBLISHED : $this->{self::STATE},
            self::DATA => $_data,
            self::SITE_ID => $this->{self::SITE_ID},
        ];


        return $data;
    }

    /**
     * Загрузка всех компонентов в локальное хранилище
     */
    public static function loadAllComponents()
    {
        /**  @var $storeManager Store */
        $storeManager = \App::make(Store::class);
        $storeManager->pushCollection(self::class, self::where([
            self::SITE_ID => DomainManager::getSiteId(),
        ])->get());
    }

    /**
     * Отображение HTML
     *
     * Метод кэширует данные, если доступен Redis, кэширование будет в теге 'core'.
     *
     * Обертка над методом self::getContent()
     *
     * @param string $name имя запрошенного модуля
     * @param bool $cache кэширование сегмента html разметки после работы модуля
     * @return string
     * @throws \Throwable
     */
    public static function display($name, $cache = true)
    {
        \Auth::check();
        $key = __METHOD__ . '::' . DomainManager::getSiteId() . '::core-site-modules-' . $name . \Request::url();
        $key .= (\Auth::guest()) ? '-guest' : '-user';

        $isRedis = config('cache.default') == 'redis';
        $result = ($isRedis) ? \Cache::tags(['core'])->get($key, null) : \Cache::get($key, null);

        if ($cache === false) {
            $result = null;
        }

        if (null === $result) {
            $result = [];
            if (is_string($name)) {
                $item = self::where(function (Builder $query) use ($name) {
                    $query->where(self::NAME, $name);
                    $query->where(self::STATE, self::STATE_PUBLISHED);
                    $query->whereIn(self::SITE_ID, ['000', DomainManager::getSiteId()]);
                })->first();
                if ($item) {
                    array_push($result, self::getContent($item));
                }
            } else {
                $items = self::where(function (Builder $query) use ($name) {
                    $query->whereIn(self::NAME, $name);
                    $query->where(self::STATE, self::STATE_PUBLISHED);
                })->get();
                foreach ($items as $item) {
                    array_push($result, self::getContent($item));
                }
            }

            $result = implode(PHP_EOL, $result);
            if ($isRedis) {
                \Cache::tags(['core'])->put($key, $result, config('cache.ttl_view', 5));
            } else {
                \Cache::put($key, $result, config('cache.ttl_view', 5));
            }
        }

        return $result;
    }

    /**
     * Метод возвращает контент базовых модулей
     *
     * @param Components $module
     * @return string
     * @throws \Throwable
     */
    static function getContent(Components $module)
    {
        $result = '';
        if (self::$modules === []) {
            self::initModules();
        }
        $data = $module->getData();

        if (isset($data['data']->type->id)) {
            if (isset(self::$modules[$data['data']->type->id])) {
                if (self::$modules[$data['data']->type->id] === __CLASS__) {
                    switch ($data['data']->type->id) {
                        case 'core::html':
                            $result = (isset($data['data']->html)) ? $data['data']->html : '';
                            if (isset($data['data']->template)) {
                                if (isset($data['data']->media)) {
                                    usort($data['data']->media, function ($a, $b) {
                                        return $a->{'sort'} - $b->{'sort'};
                                    });
                                }
                                if (view()->exists($data['data']->template->id)) {
                                    $result = view($data['data']->template->id, [
                                        'module' => $module,
                                        'data' => $data,
                                    ])->render();
                                }
                            }
                            break;
                        case 'core::language':
                            $languages = config('app.languages');

                            collect(DomainManager::getAccessDomainList())->each(function ($domain) use (&$languages) {
                                if (isset($domain['lang'])) {
                                    $name = $languages[$domain['lang']];
                                    $languages[$domain['lang']] = [
                                        'id' => $domain['id'],
                                        'lang' => $domain['lang'],
                                        'url' => $domain['url'],
                                        'name' => $name,
                                        'image' => $domain['item']->getParameterByFilterData(['name' => 'FLAG'], null),
                                    ];
                                }
                            });

                            $languages = array_filter($languages, function ($lang) {
                                return ($lang['id'] != DomainManager::getSiteId());
                            });

                            view()->share([
                                'languages' => $languages,
                            ]);
                            if (isset($data['data']->template->id)) {
                                return view($data['data']->template->id)->render();
                            }
                            break;
                        case 'core::breadcrumbs':
                        case 'core::auth.login':
                        case 'core::auth.registration':
                            if (isset($data['data']->template->id)) {
                                return view($data['data']->template->id)->render();
                            }
                            break;
                        default:
                            break;
                    }
                } else {
                    /**
                     * @var $instance ModuleInterface
                     */
                    $instance = new self::$modules[$data['data']->type->id]();

                    $result = $instance->getContent($module);
                }
            }
        }

        return $result;
    }

    /**
     * Возвращает типы базовых модулей
     *
     * @return mixed
     */
    public function getModuleType()
    {
        $paths = array_first(\Config::get('view.paths'));
        $result = [
            'id' => 'core',
            'instance' => __CLASS__,
            'name' => trans('app.Общие типы'),
            'items' => [
                [
                    'id' => 'html',
                    'name' => trans('app.Общие типы') . ' :: ' . trans('app.Html содержимое'),
                    'templates' => $this->getTemplates($paths . '/modules/core/html/*.blade.php'),
                ],
                [
                    'id' => 'breadcrumbs',
                    'name' => trans('app.Общие типы') . ' :: ' . trans('app.Цепочка навигации'),
                    'templates' => $this->getTemplates($paths . '/modules/core/breadcrumbs/*.blade.php'),
                ],
                [
                    'id' => 'language',
                    'name' => trans('app.Общие типы') . ' :: ' . trans('app.Выбор языка'),
                    'templates' => $this->getTemplates($paths . '/modules/core/language/*.blade.php'),
                ],
            ],
        ];

        return $result;
    }

    /**
     * Возвращает доступные шаблоны
     *
     * Поиск по умолчанию осуществляется в: theme#ХХХ::modules.core где ХХХ код сайта
     *
     * @param string $paths
     * @return array
     */
    public function getTemplates($paths = '')
    {
        $result = [];

        //получаем доступные пользователю site_id
        $domainsCode = DomainManager::getScopeIds();

        $list = DomainManager::getAccessDomainList();
        foreach ($domainsCode as $code) {
            $_code = $code;
            $currentPath = str_replace('modules', 'public/' . $code . '/modules', $paths);

            if (isset($list[$code])) {
                $code = $list[$code]['name'];
            }
            if ($currentPath !== '') {
                $description = [];
                if (file_exists(dirname($currentPath) . '/.description.php') && $description == []) {
                    $description = include_once dirname($currentPath) . '/.description.php';
                }
                foreach (glob($currentPath) as $filename) {
                    if (!isset($result[$code])) {
                        $result[$code]['templates'] = [];
                    }
                    $tmp = explode('/', $filename);
                    $templateName = array_last($tmp);
                    $count = count($tmp);
                    if ($count >= 2) {
                        $templateType = $tmp[$count - 2];
                        $templateName = str_replace(['.blade.php'], [''], $templateName);
                        $name = $templateName;
                        if (isset($description[$templateName])) {
                            $name = $description[$templateName];
                        }
                        array_push($result[$code]['templates'], [
                            'id' => 'theme#' . $_code . '::modules.core.' . $templateType . '.' . $templateName,
                            'name' => $name,
                        ]);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Возвращает установленные моудли
     *
     * Метод кэширует данные, если доступен Redis, кэширование будет в теге 'config'.
     * Ключ кэширования: __METHOD__ . '::' . DomainManager::getSiteId()
     *
     * @return array
     */
    public static function getInstallModules()
    {
        $key = __METHOD__ . '::' . DomainManager::getSiteId();
        $isRedis = config('cache.default') == 'redis';
        $result = ($isRedis) ? \Cache::tags(['config'])->get($key, null) : \Cache::get($key, null);

        $self = new self();
        if (null === $result) {
            $result = [$self->getModuleType()];

            /**
             * @var $moduleManager ModuleManager
             */
            $moduleManager = \App::make(ModuleManager::class);

            $modules = $moduleManager->getModules();
            /**
             * @var $module ModuleInterface
             */
            foreach ($modules as $module) {
                $config = $module->getConfig();
                if (isset($config->source->class)) {
                    /**
                     * @var $instance ModuleInterface
                     */
                    $instance = $moduleManager->getInstance($config->source->class);
                    $types = $instance->getModuleType();
                    if ($types) {
                        array_push($result, $types);
                    }
                }
            }
            if ($isRedis) {
                \Cache::tags(['config'])->put($key, $result, config('cache.ttl_config', 5));
            } else {
                \Cache::put($key, $result, config('cache.ttl_config', 5));
            }
        }

        return $result;
    }

    /**
     * Определение сопоставления модулей
     *
     * <pre>
     *  [
     *    "core::html" => "FastDog\Core\Module\SiteModules",
     *    "banner::items" => "App\Modules\Banner\Banners",
     *     ........
     *  ]
     *</pre>
     *
     * @return void
     */
    public static function initModules()
    {
        $result = [];
        $modules = Components::getInstallModules();
        foreach ($modules as $module) {
            if (isset($module['items'])) {
                foreach ($module['items'] as $item) {
                    $key = $module['id'] . '::' . $item['id'];
                    $result[$key] = $module['instance'];
                }
            }
        }
        self::$modules = $result;
    }

    /**
     * Создание таблицы базы данных
     *
     * Будет создана следующая таблица:
     *
     * <pre>
     * CREATE TABLE site_modules (
     *          id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
     *          name varchar(255) NOT NULL COMMENT 'Название',
     *          site_id char(3) NOT NULL DEFAULT '000' COMMENT 'Код сайта',
     *          data json NOT NULL COMMENT 'Дополнительные параметры',
     *          view varchar(50) NOT NULL,
     *          state tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Состояние',
     *          created_at timestamp NULL DEFAULT NULL,
     *          updated_at timestamp NULL DEFAULT NULL,
     *          deleted_at timestamp NULL DEFAULT NULL,
     *          PRIMARY KEY (id),
     *          INDEX IDX_site_modules_site_id (site_id),
     *          UNIQUE INDEX site_modules_name_unique (name)
     * )
     * COMMENT = 'Реализация контейнеров HTML выводимых в публичной части сайта';
     *
     * </pre>
     *
     * @return void
     */
    public static function createDbSchema()
    {
        if (!Schema::hasTable('site_modules')) {
            Schema::create('site_modules', function (Blueprint $table) {
                $table->increments('id');
                $table->string(self::NAME)->unique()->comment('Название');
                $table->char(self::SITE_ID, 3)->default('000')->comment('Код сайта');
                $table->json(self::DATA)->comment('Дополнительные параметры');
                $table->string('view', 50);
                $table->tinyInteger(self::STATE)->default(self::STATE_NOT_PUBLISHED)->comment('Состояние');
                $table->timestamps();
                $table->softDeletes();
                $table->index(self::SITE_ID, 'IDX_site_modules_site_id');
            });
            DB::statement("ALTER TABLE `site_modules` comment 'Реализация контейнеров HTML выводимых в публичной части сайта'");
        }


    }

    /**
     * Возвращает имя события вызываемого при обработке данных при передаче на клиент в разделе администрирования
     * @return string
     */
    public function getEventAdminPrepareName(): string
    {
        return ComponentItemAdminPrepare::class;
    }

    /**
     * Возвращает описание доступных полей для вывода в колонки...
     *
     * ... метод используется для первоначального конфигурирования таблицы,
     * дальнейшие типы, порядок колонок и т.д. будут храниться в обхекте BaseTable
     *
     * @return array
     */
    public function getTableCols(): array
    {
        return [
            [
                'name' => trans('app.Название'),
                'key' => self::NAME,
                'domain' => true,
                'callback' => false,
                'link' => 'component_item',
                'extra' => true,
            ],
            [
                'name' => '#',
                'key' => 'id',
                'link' => null,
                'width' => 80,
                'class' => 'text-center',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAdminFilters(): array
    {
        $default = [
            [
                [
                    BaseFilter::NAME => self::NAME,
                    BaseFilter::PLACEHOLDER => 'Название',
                    BaseFilter::TYPE => BaseFilter::TYPE_TEXT,
                    BaseFilter::DISPLAY => false,
                    BaseFilter::OPERATOR => (new BaseOperator('LIKE', 'LIKE'))->getOperator(),
                ],
            ],
        ];

        return $default;
    }

    /**
     * Возвращает ключ доступа к ACL
     * @param string $type
     * @return string
     */
    public function getAccessKey($type = 'guest'): string
    {
        return strtolower(\App\Modules\Config\Config::class) . '::' . DomainManager::getSiteId() . '::' . $type;
    }


    /**
     * @return Collection
     */
    public function getDefaultProperties(): Collection
    {
        $result = [];

        return collect($result);
    }

    /**
     * @return array
     */
    public function getExtractParameterNames()
    {
        return ['type', 'template', 'item_id', 'html', 'data_source_item_id'];
    }
}
