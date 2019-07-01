<?php

namespace FastDog\Core\Properties\Traits;

use FastDog\Core\Models\DomainManager;
use FastDog\Core\Properties\BaseProperties;
use FastDog\Core\Properties\BasePropertiesSelectValues;
use FastDog\Core\Properties\BasePropertiesStorage;
use FastDog\Core\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait PropertiesTrait
 * @package FastDog\Core\Properties\Traits
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
trait PropertiesTrait
{
    /**
     * @var string $active_field
     */
    protected $active_field = 'show';

    /**
     * Коллекция дополнительных параметров модели
     *
     * @return Collection
     */
    public function properties(): Collection
    {
        /**
         * Параметры определенные для модели в базе
         *
         * @var $properties Collection
         */
        $properties = BaseProperties::where(function (Builder $query) {
            $query->where(BaseProperties::MODEL, $this->getModelId());
        })->orderBy(BaseProperties::SORT)->get();

        /**
         * Параметры для модели по умолчанию
         *
         * @var $defaultProperties Collection
         */
        $defaultProperties = $this->getDefaultProperties();
        $checkProperties = ($defaultProperties->count() <> $properties->count());//<-- Проверка, есть ли новые парамтеры которые нужно добавить в базу

        if ($checkProperties) {//<-- Есть новые парамтеры, добавляем...
            $createProperties = [];
            $defaultProperties->each(function (array $item) use ($properties, &$createProperties) {
                if (!$properties->contains(BaseProperties::ALIAS, $item[BaseProperties::ALIAS])) {//<-- Существует ли парамтер в базе?
                    $item[BaseProperties::MODEL] = $this->getModelId();
                    array_push($createProperties, $item);
                }
            });

            if (count($createProperties) > 0) {
                foreach ($createProperties as $createProperty) {
                    $check = BaseProperties::where([
                        BaseProperties::ALIAS => $createProperty[BaseProperties::ALIAS],
                        BaseProperties::MODEL => $createProperty[BaseProperties::MODEL],
                    ])->first();
                    if (!$check) {
                        // unset($createProperty[BaseProperties::VALUE]);
                        $newBaseProperty = BaseProperties::create($createProperty);
                        /**
                         * Добавление вариатов значений для типа - список
                         */
                        if ($newBaseProperty) {
                            switch ($newBaseProperty->{BaseProperties::TYPE}) {
                                case BaseProperties::TYPE_SELECT:
                                    $data = $newBaseProperty->getData();
                                    if (isset($data[BaseProperties::DATA]->values)) {
                                        collect($data[BaseProperties::DATA]->values)
                                            ->each(function ($value) use ($newBaseProperty) {
                                                $selectValue = BasePropertiesSelectValues::where([
                                                    BasePropertiesSelectValues::ALIAS => $value->{BasePropertiesSelectValues::ALIAS},
                                                    BasePropertiesSelectValues::PROPERTY_ID => $newBaseProperty->id,
                                                ])->first();
                                                if ($selectValue === null) {
                                                    BasePropertiesSelectValues::create([
                                                        BasePropertiesSelectValues::NAME => $value->{BasePropertiesSelectValues::NAME},
                                                        BasePropertiesSelectValues::ALIAS => $value->{BasePropertiesSelectValues::ALIAS},
                                                        BasePropertiesSelectValues::PROPERTY_ID => $newBaseProperty->id,
                                                    ]);
                                                }
                                            });
                                    }
                                    break;
                            }
                        }
                    }
                }
                /**
                 * Перегрузка параметров из базы после добавления новых
                 * @var $properties Collection
                 */
                $properties = BaseProperties::where(function (Builder $query) {
                    $query->where(BaseProperties::MODEL, $this->getModelId());
                })->orderBy(BaseProperties::SORT)->get();
            }
        }
        $result = [];
        if ($properties->count() > 0) {
            $result = $this->loadAllPropertyWithValues($properties);
        }

        return collect($result);
    }

    /**
     * @param BaseProperties $property
     * @param mixed|string|array $defaultValue
     * @return BasePropertiesStorage
     */
    protected function checkPropertyValues(BaseProperties $property, $defaultValue = ''): BasePropertiesStorage
    {
        /**
         * Исправления под значение типа список
         */
        switch ($property->{BaseProperties::TYPE}) {
            case BaseProperties::TYPE_SELECT:
                $defaultValue = (isset($defaultValue['id'])) ? $defaultValue['id'] : '';
                break;
            default:
                break;
        }
        /**
         * @var $check BasePropertiesStorage
         */
        $check = BasePropertiesStorage::where([
            BasePropertiesStorage::MODEL_ID => $property->{BaseProperties::MODEL},
            BasePropertiesStorage::PROPERTY_ID => $property->id,
            BasePropertiesStorage::ITEM_ID => $this->getId(),
        ])->first();
        if (!$check) {
            $check = BasePropertiesStorage::create([
                BasePropertiesStorage::MODEL_ID => $property->{BaseProperties::MODEL},
                BasePropertiesStorage::PROPERTY_ID => $property->id,
                BasePropertiesStorage::VALUE => $defaultValue,
                BasePropertiesStorage::ITEM_ID => $this->getId(),
            ]);
        } else {
            BasePropertiesStorage::where([
                BasePropertiesStorage::MODEL_ID => $property->{BaseProperties::MODEL},
                BasePropertiesStorage::PROPERTY_ID => $property->id,
                BasePropertiesStorage::ITEM_ID => $this->getId(),
            ])->update([
                BasePropertiesStorage::VALUE => $defaultValue,
            ]);
        }

        return $check;
    }

    /**
     * Идентификатор модели
     *
     * @return int
     */
    public function getModelId(): int
    {
        return hexdec(DomainManager::stringToColorCode(__CLASS__));
    }


    /**
     * Сохранение параметров
     *
     * @param Collection $properties
     */
    public function storeProperties(Collection $properties)
    {
        /**
         * @var $propertiesExists Collection
         */
        $propertiesExists = BaseProperties::where(function (Builder $query) {
            $query->where(BaseProperties::MODEL, $this->getModelId());
        })->orderBy(BaseProperties::SORT)->get();

        /**
         * @var $propertiesExistsValues Collection
         */
        $propertiesExistsValues = BasePropertiesStorage::where([
            BasePropertiesStorage::MODEL_ID => $this->getModelId(),
            BasePropertiesStorage::ITEM_ID => $this->getId(),
        ])->get();

        /**
         * Параметры для модели по умолчанию
         * @var $defaultProperties Collection
         */
        $defaultProperties = $this->getDefaultProperties();

        $createProperties = [];

        $properties->each(function (array $data) use (
            &$createProperties, $propertiesExists,
            $propertiesExistsValues, $defaultProperties
        ) {

            if ($data[$this->active_field] == true) {
                if (!$propertiesExists->contains(BaseProperties::ALIAS, $data[BaseProperties::ALIAS])) {
                    $data[BaseProperties::MODEL] = $this->getModelId();
                    array_push($createProperties, $data);
                } else {
                    $propertiesExists->where(BaseProperties::ALIAS, $data[BaseProperties::ALIAS])
                        ->first(function (BaseProperties $item) use ($data, $defaultProperties) {
                            if (!isset($data[BaseProperties::VALUE])) {
                                $defaultProperties->where(BaseProperties::ALIAS, $data[BaseProperties::ALIAS])
                                    ->first(function ($property) use (&$data) {
                                        $data[BaseProperties::VALUE] = $property[BaseProperties::VALUE];
                                    });
                            }
                            $this->checkPropertyValues($item, $data[BaseProperties::VALUE]);
                        });
                }
            } else {
                if (isset($data[BasePropertiesStorage::VALUE . '_id'])) {
                    $propertiesExistsValues->where('id', $data[BasePropertiesStorage::VALUE . '_id'])
                        ->first(function (BasePropertiesStorage $item) {
                            $item->delete();
                        });
                }
            }
        });
    }

    /**
     * @return Collection
     */
    public function getDefaultProperties(): Collection
    {
        return collect([]);
    }

    /**
     * @param array $filter
     * @param $default
     * @return mixed
     */
    public function getParameterByFilterData(array $filter = ['name' => ''], $default = null)
    {
        /** @var $store Store */
        $store = \App::make(Store::class);
        $upperName = mb_strtoupper($filter['name']);
        $modelKey = $this->getModelId();
        /**
         * @var $property BaseProperties
         *
         * попытка получить свойство из локального хранилища
         */
        $property = $store->get($modelKey, $upperName . '_' . $this->getId());

        if (null === $property) {

            $properties = $store->getCollection($modelKey . '_properties');
            if (null === $properties) {
                $properties = BaseProperties::where(function (Builder $query) {
                    $query->where(BaseProperties::MODEL, $this->getModelId());
                })->orderBy(BaseProperties::SORT)->get();
                $store->pushCollection($modelKey . '_properties', $properties);
            }


            $this->loadAllPropertyWithValues($properties);

            $property = $store->get($modelKey, $upperName . '_' . $this->getId());
        }

        if (isset($property[BaseProperties::TYPE])) {

            switch ($property[BaseProperties::TYPE]['id']) {
                case BaseProperties::TYPE_SELECT:
                    if (isset($property[BaseProperties::DATA]->values) && isset($property['value']['id'])) {
                        $value = collect($property[BaseProperties::DATA]->values)
                            ->where('id', $property['value'] ['id'])->first();

                        if (isset($value['alias'])) {
                            return $value['alias'];
                        }

                        return $value;
                    }
                    break;
                default:
                    if (isset($property['value'])) {
                        return $property['value'];
                    }
                    break;
            }
        }


        return $default;
    }


    /**
     * Загружает все заполненые парамтеры для модели
     * @param Collection $properties
     * @return array
     */
    public function loadAllPropertyWithValues(Collection $properties): array
    {
        $result = [];

        if ($properties->count() > 0) {
            /**
             * @var $store Store
             */
            $store = \App::make(Store::class);
            $modelKey = $this->getModelId();

            /**
             * @var $values Collection
             */
            $values = BasePropertiesStorage::where([
                BasePropertiesStorage::MODEL_ID => $modelKey,
                BasePropertiesStorage::ITEM_ID => $this->getId(),
            ])->get();

            $properties->each(function (BaseProperties &$item) use (&$result, $values, $store, $modelKey) {
                $data = $item->getData();
                $data['show'] = false;
                $data[BaseProperties::ITEM_ID] = $this->getId();
                if (isset($data[BaseProperties::DATA]->multiple)) {
                    $item->setMultiple($data[BaseProperties::DATA]->multiple);
                }
                switch ($item->{BaseProperties::TYPE}) {
                    case BaseProperties::TYPE_SELECT:
                        $data[BaseProperties::DATA]->values = $store->get($modelKey, 'select_values_' . $item->id);
                        if (null === $data[BaseProperties::DATA]->values) {
                            $data[BaseProperties::DATA]->values = [];
                            BasePropertiesSelectValues::where([
                                BasePropertiesSelectValues::PROPERTY_ID => $item->id,
                            ])->get()
                                ->each(function (BasePropertiesSelectValues $value) use (&$data) {
                                    array_push($data[BaseProperties::DATA]->values, $value->getData());
                                });
                            $store->push($modelKey, $data[BaseProperties::DATA]->values, 'select_values_' . $item->id);
                        }
                        break;
                    default:
                        $data['value'] = $item->{BaseProperties::VALUE};
                        break;
                }
                $values->where(BasePropertiesStorage::PROPERTY_ID, $item->id)
                    ->first(function (BasePropertiesStorage $value) use (&$data, $item) {
                        $data['show'] = true;
                        switch ($item->{BaseProperties::TYPE}) {
                            case BaseProperties::TYPE_SELECT:
                                if (isset($data[BaseProperties::DATA]->values)) {
                                    $data[BasePropertiesStorage::VALUE] = array_first(array_filter($data[BaseProperties::DATA]->values, function ($_value) use ($value) {
                                        return (int)$_value['id'] == (int)$value->{BasePropertiesStorage::VALUE};
                                    }));
                                    $data[BasePropertiesStorage::VALUE . '_id'] = $value->id;
                                }
                                break;
                            default:
                                $data[BasePropertiesStorage::VALUE] = $value->{BasePropertiesStorage::VALUE};
                                $data[BasePropertiesStorage::VALUE . '_id'] = $value->id;
                                break;
                        }
                    });
                array_push($result, $data);
            });


            collect($result)->each(function ($element) use (&$store, $modelKey) {
                $key = mb_strtoupper($element['alias']) . '_' . $this->getId();

                $store->push($modelKey, $element, $key);
            });

            //$store->dump();
        }

        return $result;
    }
}