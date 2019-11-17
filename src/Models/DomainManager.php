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

        $root = ($root !== null) ? $root : request()->root();

        /** @var Cache $cache */
        $cache = app()->make(Cache::class);

        if ($code === '000') {
            $domain = $cache->get('site-' . $root, function() use ($root) {
                $domain = DomainManager::where(function(Builder $query) use ($root) {
                    $query->where(DomainManager::URL, '=', $root);
                })->select(DomainManager::URL, DomainManager::CODE, DomainManager::DATA, DomainManager::LANG)
                    ->first();
                return $domain;
            }, ['core']);

            if ($domain) {
                request()->merge([
                    '_site_id' => $domain->{DomainManager::CODE},
                    'lang' => $domain->{DomainManager::LANG},
                ]);
                if ($domain->{DomainManager::LANG} !== null) {
                    app()->setLocale($domain->{DomainManager::LANG});
                }
            }
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
