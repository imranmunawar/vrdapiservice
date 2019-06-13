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

Route::group(['namespace' => 'Auth','prefix' => 'password'], function () {    
    Route::post('create', 'ResetPasswordController@create');
    Route::get('find/{token}', 'ResetPasswordController@find');
    Route::post('reset', 'ResetPasswordController@reset');
});

Route::group(['namespace' => 'Api\V1','middleware' => 'auth:api'], function () {
    
    /* Users Crud Routes */
    Route::get('users/show/{id}',      ['uses' => 'UserController@show',   'as'  => 'showUser']);
    Route::get('users/list/{type}',    ['uses' => 'UserController@index',  'as'  => 'listUsers']);
    Route::post('users',               ['uses' => 'UserController@store',  'as'  => 'createUser']);
    Route::get('users/edit/{user}',    ['uses' => 'UserController@edit',   'as'  => 'editUser']);
    Route::patch('users/update/{id}',  ['uses' => 'UserController@update', 'as'  => 'updateUser']);
    Route::delete('users/delete/{id}', ['uses' => 'UserController@destroy','as'  => 'deleteUser']);

     /* Company Crud Routes */
    Route::get('company/show/{id}',      ['uses' => 'CompanyController@show',   'as'  => 'showCompany']);
    Route::get('companies/list',         ['uses' => 'CompanyController@index',  'as'  => 'listCompanies']);
    Route::post('companies',             ['uses' => 'CompanyController@store',  'as'  => 'createCompany']);
    Route::get('company/edit/{company}', ['uses' => 'CompanyController@edit',   'as'  => 'editCompany']);
    Route::patch('company/update/{id}',  ['uses' => 'CompanyController@update', 'as'  => 'updateCompany']);
    Route::delete('company/delete/{id}', ['uses' => 'CompanyController@destroy','as'  => 'deleteCompany']);

    Route::get('users/{type}', ['uses' => 'UserController@getUsersByRole', 'as'  => 'getUsersByRole']);
    Route::post('fairs/create', ['uses' => 'FairController@store', 'as'  => 'storeFair']);

});