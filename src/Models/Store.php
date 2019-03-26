<?php

namespace FastDog\Core;

/**
 * Class Store
 * Реализация простого хранилища для получения локальных объектов без запроса к базе данных,
 * используется как Singleton, в целях оптимизации в разделах администрирования
 *
 * @package FastDog\Core
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Store
{
    /**
     * Идентификатор авторизованного пользователя
     *
     * @var null|string $user_id
     */
    protected $user_id = null;

    /**
     * Хранилище данных в контексте текущего объекта singleton`а
     *
     * @var array $data
     */
    protected static $data = [];


    /**
     * Возвращает объект из внутреннего хранилища
     *
     * @param $name
     * @param $id
     * @return null
     */
    public function get($name, $id)
    {
        if (isset(self::$data[$name][$id])) {
            return self::$data[$name][$id];
        }

        return null;
    }

    /**
     * Возвращает объект Collection из внутреннего хранилища
     *
     * @param $name
     * @return mixed|null
     */
    public function getCollection($name)
    {
        if (isset(self::$data[$name])) {
            return self::$data[$name];
        }

        return null;
    }

    /**
     * Добавление данных в хранилище
     *
     * @param $name
     * @param $item
     * @param null $key
     * @throws \Exception
     */
    public function push($name, $item, $key = null)
    {
        try {
            if ($item !== null) {
                if (null !== $key) {
                    if (isset($item->{$key})) {// <-- передано одно из полей объекта
                        self::$data[$name][$item->{$key}] = $item;
                    } else {
                        self::$data[$name][$key] = $item;// <-- произвольный ключ хранилища
                    }
                } else {
                    if (isset($item->id)) {
                        self::$data[$name][$item->id] = $item;
                    } elseif (isset($item['id'])) {
                        self::$data[$name][$item['id']] = $item;
                    }
                }
            } else {
                throw new \Exception('name=>' . $name . ', item is null');
            }
        } catch (\Exception $exception) {
            dd($exception);
        }

    }

    /**
     * @param $name
     * @param $collection
     */
    public function pushCollection($name, $collection): void
    {
        self::$data[$name] = $collection;
    }

    /**
     * Отладка
     */
    public function dump(): void
    {
        var_dump(self::$data);
    }
}