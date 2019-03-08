<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('/database/index', 'DatabaseController@index');
    $router->post('/database/editFieldEngineType', 'DatabaseController@editFieldEngineType');
    $router->get('/database/searchTable', 'DatabaseController@searchTable');
    $router->get('/database/updatePrimaryName', 'DatabaseController@updatePrimaryName');
    $router->get('/database/updateTableStruct', 'DatabaseController@updateTableStruct');

});
