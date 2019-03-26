<?php

Route::group([
    'prefix' => config('core.admin_path', 'admin'),
    'middleware' => ['web'],
], function () {
    /**
     * Служебные маршруты по умолчанию
     */
    Route::get('/', '\FastDog\Core\Http\Controllers\AdminController@getIndex');
    Route::get('/login', '\FastDog\Core\Http\Controllers\AdminController@getLogin');
    Route::post('/login', '\FastDog\Core\Http\Controllers\AdminController@postLogin');
    Route::get('/menu', '\FastDog\Core\Http\Controllers\AdminController@getMenu');
    Route::get('/desktop', '\FastDog\Core\Http\Controllers\AdminController@getDesktop');
    Route::post('/desktop-sort', '\FastDog\Core\Http\Controllers\AdminController@postDesktopSort');

});