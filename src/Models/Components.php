<?php

namespace FastDog\Core\Models;


use FastDog\Config\Events\Components\ComponentItemAdminPrepare;
use FastDog\Core\Events\GetComponentType;
use FastDog\Core\Interfaces\ModuleInterface;
use FastDog\Core\Media\Interfaces\MediaInterface;
use FastDog\Core\Media\Traits\MediaTraits;
use FastDog\Core\Properties\Interfases\PropertiesInterface;
use FastDog\Core\Properties\Traits\PropertiesTrait;
use FastDog\Core\Store;
use FastDog\Core\Table\Filters\BaseFilter;
use FastDog\Core\Table\Filters\Operator\BaseOperator;
use FastDog\Core\Table\Interfaces\TableModelInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Реализация контейнеров
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
    protected $table = 'components';

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
     * @deprecated
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
     * @deprecated
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
     * @deprecated
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
                $item = self::where(function(Builder $query) use ($name) {
                    $query->where(self::NAME, $name);
                    $query->where(self::STATE, self::STATE_PUBLISHED);
                    $query->whereIn(self::SITE_ID, ['000', DomainManager::getSiteId()]);
                })->first();
                if ($item) {
                    array_push($result, self::getContent($item));
                }
            } else {
                $items = self::where(function(Builder $query) use ($name) {
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
     * @deprecated
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
                                    usort($data['data']->media, function($a, $b) {
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

                            collect(DomainManager::getAccessDomainList())->each(function($domain) use (&$languages) {
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

                            $languages = array_filter($languages, function($lang) {
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
     *
     */
    public function getModuleType()
    {
        $result = [];

        event(new GetComponentType($result));

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
    public static function getTemplates($paths = '')
    {
        $result = [];

        //получаем доступные пользователю site_id
        $domainsCode = DomainManager::getScopeIds();

        $list = DomainManager::getAccessDomainList();
        foreach ($domainsCode as $code) {
            $_code = $code;
            $currentPath = str_replace('/fast_dog/', '/fast_dog/' . $code . '/', $paths);

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
        return app()->make(Cache::class)->get(__METHOD__ . '::' . DomainManager::getSiteId(), function() {

            return (new Components())->getModuleType();

        }, ['config']);
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
                'name' => trans('config::forms.components.general.fields.name'),
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
                    BaseFilter::DISPLAY => true,
                    BaseFilter::OPERATOR => (new BaseOperator('LIKE', 'LIKE'))->getOperator(),
                ],
            ],
        ];

        return $default;
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
