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

Route::group(['namespace'=>'Api\v1', 'prefix'=>'v1'], function(){
    /*  Authentication  */
    Route::post('signup', 'AuthController@signup');
    Route::post('signin', 'AuthController@signin');
});

Route::group(['namespace'=>'Api\v1', 'prefix'=>'v1', 'middleware'=>['auth:api', 'api_header']], function() {
    /* User */
    Route::get('logout', 'AuthController@logout');
    Route::post('file/{id}', 'FileController@update');
    Route::resource('file', 'FileController');
});
