<?php

Route::group([
    'prefix' => config('core.admin_path', 'admin'),
    'middleware' => ['web'],
], function () {
    /**
     * Служебные маршруты по умолчанию
     */
    Route::get('/', '\FastDog\Core\Controllers\AdminController@getIndex');
    Route::get('/login', '\FastDog\Core\Controllers\AdminController@getLogin');
    Route::post('/login', '\FastDog\Core\Controllers\AdminController@postLogin');
    Route::get('/menu', '\FastDog\Core\Controllers\AdminController@getMenu');
    Route::get('/desktop', '\FastDog\Core\Controllers\AdminController@getDesktop');
    Route::post('/desktop-sort', '\FastDog\Core\Controllers\AdminController@postDesktopSort');

});