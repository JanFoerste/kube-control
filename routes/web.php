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

/**
 * Routes without authentication
 *
 * @var \Laravel\Lumen\Routing\Router $router
 */
$router->group(['middleware' => 'guest'], function () use ($router) {

    $router->post('auth/token', ['as' => 'getToken', 'uses' => 'AuthController@authenticate']);

});

/**
 * Authenticated routes
 */
$router->group(['middleware' => 'auth'], function () use ($router) {

    $router->post('auth/renew', ['as' => 'renewToken', 'uses' => 'AuthController@renewToken']);

    $router->get('/', function (\Illuminate\Http\Request $request) use ($router) {
        return $router->app->version();
    });

    // /user/ routes
    $router->group(['prefix' => 'users'], function () use ($router) {

        $router->get('/', ['as' => 'getUsers', 'uses' => 'UserController@getUsers']);
        $router->get('{username}', ['as' => 'getUser', 'uses' => 'UserController@getUser']);
        $router->patch('{username}', ['as' => 'modifyUser', 'uses' => 'UserController@modifyUser']);
        $router->delete('{username}', ['as' => 'deleteUser', 'uses' => 'UserController@deleteUser']);

    });

    // Routes only accessible by admins
    $router->group(['middleware' => 'admin'], function () use ($router) {

        $router->post('users', ['as' => 'createUser', 'uses' => 'UserController@createUser']);

    });

});



