<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 03.12.2016
 * Time: 12:08
 */

namespace FastDog\Core\Traits;


use FastDog\Core\Models\DomainManager;
use Illuminate\Database\Eloquent\Builder;

/**
 * Типаж состоянии моделей
 *
 * @package FastDog\Core\CoreTrait
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
trait StateTrait
{
    /**
     * Модель не в корзине
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefault(Builder $query)
    {
        return $query->where(self::STATE, '!=', self::STATE_IN_TRASH);
    }

    /**
     * Модель опубликована
     *
     * Учитывается код сайта
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefaultSite(Builder $query)
    {
        $query->whereIn(self::SITE_ID, DomainManager::getScopeIds());

        return $query->where(self::STATE, '=', self::STATE_PUBLISHED);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefaultAdmin(Builder $query)
    {
        $query->whereIn(self::SITE_ID, DomainManager::getScopeIds());

        return $query->where(self::STATE, '=', self::STATE_PUBLISHED);
    }

    /**
     * Модель опубликована
     *
     * Учитывается код сайта
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActiveCurrentSite(Builder $query)
    {
        $query->whereIn(self::SITE_ID, DomainManager::getSiteId());

        return $query->where(self::STATE, '=', self::STATE_PUBLISHED);
    }

    /**
     * Модель не в корзине
     *
     * Учитывается код сайта
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        $query->whereIn(self::SITE_ID, DomainManager::getScopeIds());

        return $query->where(self::STATE, '!=', self::STATE_IN_TRASH);
    }

    /**
     * Модель в корзине
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTrash(Builder $query)
    {
        return $query->where(self::STATE, self::STATE_IN_TRASH);
    }

    /**
     * Кол-во позиций по сотояниям
     *
     * Метод возвращает статистическую информацию по модели
     *
     * <pre>
     * [
     *  'total' => 'Общее количество записей',
     *  'published' => 'Опубликовано',
     *  'published_percent' => 'Опубликовано в процентном соотношение от общего количества',
     *  'not_published' => 'Не опубликовано',
     *  'not_published_percent' => 'Не опубликовано в процентном соотношение от общего количества',
     *  'in_trash' => 'В корзине',
     *  'in_trash_percent' => 'В корзине',
     *  'deleted' => 'Удалено',
     *  'deleted_percent' =>  'Удалено в процентном соотношение от общего количества',
     *  'cache_tags' => 'Поддержка тегов при кеширование'
     * ];
     * </pre>
     * @return array
     */
    public static function getStatistic()
    {
        $countPublished = self::where(function (Builder $query) {
            $query->where(self::STATE, self::STATE_PUBLISHED);
            $query->where(self::SITE_ID, DomainManager::getSiteId());
        })->count();

        $countNotPublished = self::where(function (Builder $query) {
            $query->where(self::STATE, self::STATE_NOT_PUBLISHED);
            $query->where(self::SITE_ID, DomainManager::getSiteId());
        })->count();

        $countInTrash = self::where(function (Builder $query) {
            $query->where(self::STATE, self::STATE_IN_TRASH);
            $query->where(self::SITE_ID, DomainManager::getSiteId());
        })->count();

        $countDeleted = self::where(function (Builder $query) {
            $query->where(self::SITE_ID, DomainManager::getSiteId());
        })->whereNotNull('deleted_at')->withTrashed()->count();

        $total = self::where(function (Builder $query) {
            $query->where(self::SITE_ID, DomainManager::getSiteId());
        })->withTrashed()->count();

        $result = [
            'total' => $total,
            'published' => $countPublished,
            'published_percent' => ($total > 0) ? round((($countPublished * 100) / $total), 2) : 0,
            'not_published' => $countNotPublished,
            'not_published_percent' => ($total > 0) ? round((($countNotPublished * 100) / $total), 2) : 0,
            'in_trash' => $countInTrash,
            'in_trash_percent' => ($total > 0) ? round((($countInTrash * 100) / $total), 2) : 0,
            'deleted' => $countDeleted,
            'deleted_percent' => ($total > 0) ? round((($countDeleted * 100) / $total), 2) : 0,
            'cache_tags' => (env('CACHE_DRIVER') === 'redis') ? 'Y' : 'N',
        ];

        return $result;
    }

    /**
     * Общие и доступные по коду модели
     *
     * Доступ к общим и доступным для сайта моделям
     *
     * @param Builder $query
     * @return mixed
     */
    public function scopeSite($query)
    {
        return $query->whereIn(self::SITE_ID, ['000', DomainManager::getSiteId()]);
    }
}