<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 026 26.03.19
 * Time: 16:46
 */

namespace FastDog\Core\Events;

use FastDog\Core\Interfaces\AdminPrepareEventInterface;
use FastDog\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Class JsonPrepare
 * @package FastDog\Core\Events
 * @version 0.1.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class JsonPrepare implements AdminPrepareEventInterface
{
    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @param array $data ['success' => true, 'items' => [], 'cols' => []]
     */
    public function __construct(array &$data)
    {
        $this->data = &$data;

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
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Возвращает текущую модель
     *
     * @return Model
     */
    public function getItem(): Model
    {
        return (new BaseModel());
    }

    /**
     * Возвращает результирующий массив который будет передан на клиент в виде json объекта
     *
     * @return array
     */
    public function getResult(): array
    {
        return [];
    }

    /**
     * Устанавливает результирующий массив который будет передан на клиент в виде json объекта
     *
     * @param array $result
     */
    public function setResult(array $result): void
    {
        // TODO: Implement setResult() method.
    }
}