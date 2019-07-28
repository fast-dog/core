<?php

namespace FastDog\Core\Events;

use FastDog\Core\Models\BaseModel;
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
     * @var BaseModel $item
     */
    protected $item;

    /**
     * @var BaseModel $oldItem
     */
    protected $oldItem;


    public function __construct(BaseModel $item, BaseModel $oldItem)
    {
        $this->setItem($item);
        $this->setOldItem($oldItem);
    }

    /**
     * @return BaseModel
     */
    public function getItem(): Model
    {
        return $this->item;
    }

    /**
     * @param BaseModel $item
     */
    public function setItem(BaseModel $item): void
    {
        $this->item = $item;
    }

    /**
     * @return BaseModel
     */
    public function getOldItem(): BaseModel
    {
        return $this->oldItem;
    }

    /**
     * @param BaseModel $oldItem
     */
    public function setOldItem(BaseModel $oldItem): void
    {
        $this->oldItem = $oldItem;
    }


}
