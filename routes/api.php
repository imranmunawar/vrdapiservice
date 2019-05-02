<?php

Route::group(['namespace' => 'Api\V1', 'as' => 'api.', 'middleware' => ['api']], function () {
    /* Users Crud Routes */
    Route::get('users/list/{type}', ['uses' => 'UserController@index', 'as'  => 'listUsers']);
    Route::post('users', ['uses' => 'UserController@store', 'as'  => 'createUser']);
    Route::get('users/edit/{user}',  ['uses' => 'UserController@edit','as'    => 'editUser']);
    Route::patch('users/update/{id}',  ['uses' => 'UserController@update','as'  => 'updateUser']);
    Route::delete('users/delete/{id}', ['uses' => 'UserController@destroy','as' => 'deleteUser']);

});