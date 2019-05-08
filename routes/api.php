<?php
use Illuminate\Http\Request;

Route::group(['prefix' => 'auth','namespace' => 'Api\V1'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
  
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::group(['namespace' => 'Api\V1','middleware' => 'auth:api'], function () {
    
    /* Users Crud Routes */
    Route::get('users/show/{id}', ['uses' => 'UserController@show', 'as'  => 'showUser']);
    Route::get('users/list/{type}', ['uses' => 'UserController@index', 'as'  => 'listUsers']);
    Route::post('users', ['uses' => 'UserController@store', 'as'  => 'createUser']);
    Route::get('users/edit/{user}',  ['uses' => 'UserController@edit','as'    => 'editUser']);
    Route::patch('users/update/{id}',  ['uses' => 'UserController@update','as'  => 'updateUser']);
    Route::delete('users/delete/{id}', ['uses' => 'UserController@destroy','as' => 'deleteUser']);

});