<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');

// Routing Menu Products
Route::get('register', 'RegisterController@index');
Route::post('register', 'RegisterController@storeData');

Route::get('tes', 'TestController@index');
