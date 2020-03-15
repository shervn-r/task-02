<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('/sign_in', [
        'uses' => 'AuthController@sign_in'
    ]);

    $router->post('/sign_up', [
        'uses' => 'AuthController@sign_up'
    ]);
});

$router->group(['prefix' => 'urls'], function () use ($router) {
    $router->post('/', [
        'middleware' => 'auth',
        'uses' => 'UrlController@store'
    ]);

    $router->post('/sign_up', [
        'uses' => 'AuthController@sign_up'
    ]);
});

$router->group(['domain' => '{subdomain}.localhost'], function () use ($router) {
    $router->get('/r/{short_url_identifier}', [
        'uses' => 'UrlController@show'
    ]);
});
