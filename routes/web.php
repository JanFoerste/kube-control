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

$router->group(
    ['middleware' => 'guest'],
    function () use ($router) {

        $router->post('/auth/login', 'AuthController@authenticate');

    }
);

$router->group(
    ['middleware' => 'auth'],
    function () use ($router) {

        $router->post('/auth/renew', 'AuthController@renewToken');

        $router->get('/', function (\Illuminate\Http\Request $request) use ($router) {
            return $router->app->version();
        });

    }
);



