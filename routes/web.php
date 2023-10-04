<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\usersController;

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

// $router->group(['middleware' => 'role:admin'], function ($router) {
    $router->get('/users', 'usersController@Index');
    $router->post('users/create/{role}', 'usersController@create');
    $router->get('users/show/{id}', 'usersController@show');
    $router->get('users/update/{id}', 'usersController@update');
// });
