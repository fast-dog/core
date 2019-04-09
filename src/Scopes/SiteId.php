<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 16.12.2016
 * Time: 18:03
 */

namespace FastDog\Core\Scopes;

use FastDog\Core\Models\DomainManager;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @package FastDog\Core\Scopes
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class SiteId implements Scope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $filter = DomainManager::getScopeIds();
        $builder->whereIn('site_id', $filter);
    }
}