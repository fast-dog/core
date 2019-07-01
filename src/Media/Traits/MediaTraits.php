<?php

namespace FastDog\Core\Media\Traits;

use FastDog\Core\Media\BaseMedia;
use FastDog\Core\Models\ModuleManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Trait MediaTraits
 *
 * @package FastDog\Core\Media\Traits
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
trait MediaTraits
{
    /**
     * @return Collection
     */
    public function getMedia(): Collection
    {
        $result = [];

        BaseMedia::where([
            BaseMedia::ITEM_ID => $this->id,
            BaseMedia::MODEL_ID => $this->getModelId(),
        ])->get()->each(function (BaseMedia $item) use (&$result) {
            array_push($result, $item->getData());
        });

        if (count($result) == 0) {
            array_push($result, [
                'id' => 0, 'value' => '',
                'type' => 'file',
                'sort' => 100, 'hash' => '',
            ]);
        }

        return collect($result);
    }

    /**
     * Сохранение\привязка загруженных файлов
     *
     * @param Collection $media
     */
    public function storeMedia(Collection $media)
    {
        $existMediaItems = $this->getMedia();
        /**
         * Справочкники
         *
         * @var $moduleManager ModuleManager
         */
        $moduleManager = \App::make(ModuleManager::class);
        if ($moduleManager->hasModule('App\Modules\Media\Media')) {
            $removeMediaItemsHash = [];

            $media->each(function ($item) use ($existMediaItems, &$removeMediaItemsHash) {
                if ($item['value'] !== '') {
                    switch ($item['type']) {
                        case BaseMedia::TYPE_FILE:
                            if ($item['hash'] !== '' && $item['hash'] !== null) {
                                $hash = md5($item['data']['hash']);
                                array_push($removeMediaItemsHash, $hash);
                                $galleryItem = GalleryItem::where([
                                    GalleryItem::HASH => $hash,
                                ])->first();
                                if (!$galleryItem) {
                                    /*
                                     * Файл не найден в базе данных, возможно был загружен в обход файлового менеджера,
                                     * по FTP или в рамках интеграции верстки, нужно зарегистрировать файлы
                                     */
                                    $galleryItem = GalleryItem::create([
                                        GalleryItem::PARENT_TYPE => GalleryItem::TYPE_UNDEFINED,
                                        GalleryItem::PARENT_ID => 0,
                                        GalleryItem::PATH => $item['value'],
                                        GalleryItem::DATA => json_encode($item['data']),
                                        GalleryItem::HASH => $hash,
                                        GalleryItem::SITE_ID => DomainManager::getSiteId(),
                                    ]);
                                }

                                if ($galleryItem) {
                                    $check = BaseMedia::firstOrCreate([
                                        BaseMedia::ITEM_ID => $this->id,
                                        BaseMedia::MODEL_ID => $this->getModelId(),
                                        BaseMedia::HASH => $hash,
                                    ]);
                                    $data = [
                                        BaseMedia::MEDIA_ID => $galleryItem->id,
                                        BaseMedia::SORT => (int)$item['sort'],
                                        BaseMedia::HASH => $hash,
                                        BaseMedia::DATA => json_encode([
                                            'description' => (isset($item['description'])) ? $item['description'] : '',
                                            'hash' => $item['data']['hash'],
                                        ]),
                                    ];

                                    if ($check) {
                                        BaseMedia::where('id', $check->id)->update($data);
                                    } else {
                                        $data[BaseMedia::ITEM_ID] = $this->id;
                                        $data[BaseMedia::MODEL_ID] = $this->getModelId();
                                        BaseMedia::create($data);
                                    }
                                }
                            }
                            break;
                    }
                }
            });

            $countMediaItems = BaseMedia::where([
                BaseMedia::ITEM_ID => $this->id,
                BaseMedia::MODEL_ID => $this->getModelId(),
            ])->count();

            /*
             * Удаление привязок к файлам
             */
            if (count($removeMediaItemsHash)) {//<-- Удаление методом исключчения, то что не передано в форме
                BaseMedia::where(function (Builder $query) use ($removeMediaItemsHash) {
                    $query->where(BaseMedia::MODEL_ID, $this->getModelId());
                    $query->where(BaseMedia::ITEM_ID, $this->id);
                    $query->whereNotIn(BaseMedia::HASH, $removeMediaItemsHash);
                })->delete();
            } else if (count($removeMediaItemsHash) === 0 && $countMediaItems > 0) {//<-- Если форма пустая, но у модели есть файлы, удаляем все файлы
                BaseMedia::where([
                    BaseMedia::ITEM_ID => $this->id,
                    BaseMedia::MODEL_ID => $this->getModelId(),
                ])->delete();
            }
        }
    }
}