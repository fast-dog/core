<?php

namespace FastDog\Core\Events;


use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Core\Models\Domain;
use Illuminate\Database\Eloquent\Model;

/**
 * Редактирование домена
 *
 * @package App\Modules\Config\Events
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DomainsItemAdminPrepare implements AdminPrepareEventInterface
{
    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var array $result
     */
    protected $result = [];

    /**
     * @var Domain $item
     */
    protected $item;

    /**
     * DomainsItemAdminPrepare constructor.
     * @param array $data
     * @param Domain $item
     * @param array $result
     */
    public function __construct(array &$data, Domain &$item, array &$result)
    {
        $this->data = &$data;
        $this->item = &$item;
        $this->result = &$result;
    }

    /**
     * @return Domain
     */
    public function getItem(): Model
    {
        return $this->item;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array $result
     * @return void
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }
}
