<?php

namespace FastDog\Core\Models;


use Illuminate\Database\Eloquent\Builder;

/**
 * Управление доступными сайтами
 *
 * @package FastDog\Modules\Config\Entity
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainManager extends Domain
{
    /**
     * @var array $domains
     */
    public static $domains = [];

    /**
     * Текущий код сайта
     *
     * Метод кэширует данные, если доступен Redis, кэширование будет в теге 'core'.
     * Ключ кэширования: md5('site-' . \Request::root())
     *
     * @param null $root
     * @return string
     */
    public static function getSiteId($root = null): string
    {
        $code = request()->input('_site_id', '000');

        try {
            $root = ($root !== null) ? $root : request()->root();
            $key = md5('site-' . $root);

            if ($code === '000') {
                $code = (config('cache.default') == 'redis') ? \Cache::tags(['core'])->get($key, null) : \Cache::get($key, null);

                if ($code === null) {
                    $domains = DomainManager::where(function(Builder $query) use ($root) {
                        $query->where(DomainManager::URL, '=', $root);
                    })->select(DomainManager::URL, DomainManager::CODE, DomainManager::DATA, DomainManager::LANG)->first();

                    if (null !== $domains) {
                        $code = $domains->{DomainManager::CODE};
                        \Request::merge([
                            '_site_id' => $domains->{DomainManager::CODE},
                            'lang' => $domains->{DomainManager::LANG},
                        ]);
                    }
                    if (config('cache.default') == 'redis') {
                        \Cache::tags(['core'])->put($key, $code, config('cache.ttl_core', 30));

                        if (isset($domains->{DomainManager::LANG})) {
                            \Cache::tags(['core'])->put($key . '-lang', $domains->{DomainManager::LANG}, config('cache.ttl_core', 30));
                        }
                    } else {
                        \Cache::put($key, $code, config('cache.ttl_core', 30));
                        if (isset($domains->{DomainManager::LANG})) {
                            \Cache::put($key . '-lang', $domains->{DomainManager::LANG}, config('cache.ttl_core', 30));
                        }
                    }
                } else {
                    \Request::merge([
                        '_site_id' => $code,
                    ]);
                }
            }
            $lang = (config('cache.default') == 'redis') ? \Cache::tags(['core'])->get($key . '-lang', null) : \Cache::get($key . '-lang', null);
            if ($lang !== null) {
                \App::setLocale($lang);
            }
        } catch (\Exception $exception) {

        }


        return (!$code) ? '000' : $code;
    }

    /**
     * Регистрирует директорию шаблонов сайта
     * @return void
     */
    public static function initView(): void
    {
        //шаблоны по умолчанию, заглушки и т.д.
        view()->addNamespace('theme', realpath(base_path(config('view.domain_dir', 'resources/views/vendor/fast_dog') . '/000/')));

        foreach (self::getScopeIds() as $code) {
            // шаблоны по доменам
            view()->addNamespace('theme#' . $code, realpath(base_path(config('view.domain_dir', 'resources/views/vendor/fast_dog') . '/' . $code . '/')));
        }
    }

    /**
     * Определение главного сайта
     *
     * @return bool
     */
    public static function checkIsDefault(): bool
    {
        return (self::getSiteId() === config('app.default_domain_code'));
    }

    /**
     * Доступные коды сайтов
     *
     * @return array
     */
    public static function getAccessDomainList(): array
    {
        if (self::$domains == []) {
            $items = self::get();
            $result = [['id' => '000', 'name' => 'Общий доступ']];
            foreach ($items as $item) {
                $result[] = [
                    'id' => $item->{self::CODE},
                    self::NAME => $item->{self::NAME},
                    self::URL => $item->{self::URL},
                    self::LANG => $item->{self::LANG},
                    'item' => $item,
                ];
            }
            self::$domains = $result;
        }

        return self::$domains;
    }

    /**
     * Доступные локализации
     *
     * @return array
     */
    public static function getAllowLang(): array
    {
        return [
            ['id' => 'ru', 'name' => 'Русский'],
//            ['id' => 'en', 'name' => 'English'],
//            ['id' => 'de', 'name' => 'Немецкий'],
        ];
    }

    /**
     * Суффикс домена
     *
     * Для раздела администрирования, возвращает код цвета и данные по запрошенному коду сайта
     *
     * @param $code
     * @return array
     */
    public static function getDomainSuffix($code): array
    {
        $list = self::getAccessDomainList();
        if (null === $code) {
            $code = '000';
        }
        if ($list) {
            $result = array_first(array_filter($list, function($item) use ($code) {
                return $code == $item['id'];
            }));

            return [
                'color' => self::stringToColorCode($result['name']),
                'code' => $code,
                'text' => ($result == null) ? 'Неопределено' : $result,
            ];
        }

        return [
            'color' => self::stringToColorCode('Неопределено'),
            'code' => $code,
            'text' => 'Неопределено',
        ];
    }

    /**
     * @param $str
     * @return string
     */
    public static function stringToColorCode($str)
    {
        if (is_array($str)) {
            $str = implode('#', $str);
        }
        $code = dechex(crc32($str));
        $code = substr($code, 0, 6);

        return $code;
    }

    /**
     * Коды доступных сайтов
     *
     * Возвращает массив кодов для использования в scope`s
     *
     * @return array
     */
    public static function getScopeIds(): array
    {
        $result = ['000' => '000'];
        $default = DomainManager::getSiteId();
        if (self::checkIsDefault()) {
            $allDomain = self::getAccessDomainList();
            foreach ($allDomain as $item) {
                $result[$item['id']] = $item['id'];
            }
        } else {
            $result[$default] = $default;
        }

        return $result;
    }

    /**
     * Возвращает путь к публичным файлам шаблона
     *
     * @return string
     */
    public static function getAssetPath()
    {
        return '/themes/' . self::getSiteId() . '/default/assets/';
    }
}
