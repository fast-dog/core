<?php

namespace FastDog\Core\Interfaces;

use Illuminate\Http\Request;

/**
 * Обработка запроса в публичной части сайта
 *
 * @package FastDog\Core\Interfaces
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
interface PrepareContent
{
    /**
     * Обработка публичного запроса
     *
     * @param Request $request
     * @param mixed $item объект активного пункта меню
     * @param $data
     * @return mixed|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function prepareContent(Request $request, $item, $data): \Illuminate\View\View;
}