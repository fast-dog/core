<?php

namespace FastDog\Core\Events;


/**
 * Class GetComponentTypeFields
 * @package FastDog\Core\Events
 * @version 0.1.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class GetComponentTypeFields
{
    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * GetComponentType constructor.
     * @param array $data
     */
    public function __construct(array &$data )
    {
        $this->data = &$data;
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
