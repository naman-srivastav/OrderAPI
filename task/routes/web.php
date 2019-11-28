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

//Get all orders
$router->get('orders', 'OrdersController@index');
// Create new order
$router->post('orders', 'OrdersController@create');
// Update order
$router->patch('orders/{id}', 'OrdersController@update');