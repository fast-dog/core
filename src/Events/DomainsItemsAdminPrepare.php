<?php

namespace FastDog\Core\Events;



use FastDog\Core\Models\DomainManager;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Список доменов
 *
 * @package FastDog\Modules\Config\Events
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainsItemsAdminPrepare
{
    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var DomainManager $items
     */
    protected $items;

    /**
     * DomainsItemsAdminPrepare constructor.
     * @param array $data ['success' => true, 'items' => [], 'cols' => []]
     * @param array|LengthAwarePaginator $items
     */
    public function __construct(array &$data, &$items)
    {
        $this->data = &$data;
        $this->items = &$items;
    }

    /**
     * @return DomainManager
     */
    public function getItem()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}