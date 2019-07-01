<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 13.04.2018
 * Time: 18:11
 */

namespace FastDog\Core\Media\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface MediaInterface
 * @package FastDog\Core\Media\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface MediaInterface
{
    /**
     * Возвращает связанные с текущей моделью файлы
     *
     * @return Collection
     */
    public function getMedia(): Collection;

    /**
     * Идентификатор модели
     * @return int
     */
    public function getModelId(): int;

    /**
     * Сохранение\привязка загруженных файлов
     *
     * @param Collection $media
     */
    public function storeMedia(Collection $media);


}