<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/link', 'Api\UserController@createAuthLink');
Route::get('/login', 'Api\UserController@login');
Route::post('/refreshAccessToken', 'Api\UserController@refreshAccessToken');
Route::get('/users', 'Api\UserController@getAllUsers')->middleware(['tokenCheck']);
