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

Route::get('/', function () { return redirect('/admin/home'); });

Auth::routes();

// Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
// Route::post('login', 'Auth\LoginController@login')->name('auth.login');
// Route::post('logout', 'Auth\LoginController@logout')->name('auth.logout');

// // Change Password Routes...
// Route::get('change_password', 'Auth\ChangePasswordController@showChangePasswordForm')->name('auth.change_password');
// Route::patch('change_password', 'Auth\ChangePasswordController@changePassword')->name('auth.change_password');

// // Password Reset Routes...
// Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('auth.password.reset');
// Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('auth.password.reset');
// Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
// Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('auth.password.reset');

// Route::get('/home', 'HomeController@index')->name('home');



// Route::namespace('Api\V1')->group(function () {
//     Route::get('/users', 'UserController@index')->name('users');
//     Route::post('users/create',[ 'uses'            => 'UserController@store', 'as'  => 'create_user']);
//     Route::get('users/{user}/edit',['uses'         => 'UserController@edit','as'    => 'edit_user']);
//     Route::patch('users/update/{id?}',['uses'      => 'users@update','as'           => 'update_user']);
//     Route::delete('users/delete/{branch?}',['uses' => 'UserController@destroy','as' => 'delete_user']);
// });


// Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



