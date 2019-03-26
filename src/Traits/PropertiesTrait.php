<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 11.12.2016
 * Time: 17:32
 */

namespace FastDog\Core\Traits;

/**
 * Реализация типажа дополнительных данных для отображения в публичной части.
 *
 * В стадии разработки - устарел, смотрите FastDog\Core\Properties\Traits\PropertiesTrait
 *
 * @package FastDog\Core\CoreTrait
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 * @deprecated
 */
trait PropertiesTrait
{
    /**
     * Получение данных
     *
     * Возвращает отсортированные по ключу SORT данные хранящиеся в $data модели
     *
     *
     * @param object $data
     * @return array
     */
    public function getSampleProperties($data)
    {
        $result = [];
        if (isset($data->properties)) {
            foreach ($data->properties as $property) {
                array_push($result, [
                    'name' => $property->name,
                    'value' => $property->value,
                    'sort' => $property->sort,
                ]);
            }
        }
        if ($result !== []) {
            usort($result, function ($a, $b) {
                return $a['sort'] - $b['sort'];
            });
        } else {
            array_push($result, [
                'name' => '',
                'value' => '',
                'sort' => 100,
            ]);
        }

        return $result;
    }
}