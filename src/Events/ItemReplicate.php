<?php

namespace FastDog\Core\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemReplicate
 * @package FastDog\Core\Events
 * @version 0.1.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class ItemReplicate
{
    /**
     * @var Model $item
     */
    protected $item;

    /**
     * @var Model $oldItem
     */
    protected $oldItem;


    public function __construct(Model $item, Model $oldItem)
    {
        $this->setItem($item);
        $this->setOldItem($oldItem);
    }

    /**
     * @return Model
     */
    public function getItem(): Model
    {
        return $this->item;
    }

    /**
     * @param Model $item
     */
    public function setItem(Model $item): void
    {
        $this->item = $item;
    }

    /**
     * @return Model
     */
    public function getOldItem(): Model
    {
        return $this->oldItem;
    }

    /**
     * @param Model $oldItem
     */
    public function setOldItem(Model $oldItem): void
    {
        $this->oldItem = $oldItem;
    }


}
