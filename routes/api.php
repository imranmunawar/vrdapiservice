<?php

Route::group(['namespace' => 'Api\V1', 'as' => 'api.', 'middleware' => ['api']], function () {
    Route::resource('users', 'UserController');

});