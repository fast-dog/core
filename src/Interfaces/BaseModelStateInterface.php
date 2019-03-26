<?php

namespace FastDog\Core\Interfaces;

/**
 * Interface BaseModelStateInterface
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface BaseModelStateInterface
{
    /**
     * Состояние модели
     *
     * Возможные значения self::STATE_PUBLISHED, self::STATE_NOT_PUBLISHED, self::STATE_IN_TRASH
     *
     * @const int
     */
    const STATE = 'state';

    /**
     * Состояние: Опубликовано
     *
     * @const int
     */
    const STATE_PUBLISHED = 1;

    /**
     * Состояние: Не опубликовано
     *
     * @const int
     */
    const STATE_NOT_PUBLISHED = 0;

    /**
     * Состояние: В корзине
     *
     * @const int
     */
    const STATE_IN_TRASH = 2;

}