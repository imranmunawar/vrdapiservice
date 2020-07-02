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

// Route::get('/', function () { return redirect('/home'); });

// Auth::routes();
// Route::impersonate();

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login')->name('auth.login');
Route::post('logout', 'Auth\LoginController@logout')->name('auth.logout');

// // Change Password Routes...
// Route::get('change_password', 'Auth\ChangePasswordController@showChangePasswordForm')->name('auth.change_password');
// Route::patch('change_password', 'Auth\ChangePasswordController@changePassword')->name('auth.change_password');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('auth.password.reset');

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['namespace' => 'Api\V1'], function () { 

    // Get Fair Job detailt
    Route::get('fill/recruiters',[
        'uses' => 'CompanyJobController@fillRecruiters',
        'as'   => 'fillRecruiters'
    ]); 


    // Get Fair Job detailt
    Route::get('front/job/detail/{job_id}/{candidate_id?}',[
        'uses' => 'CompanyJobController@detail',
        'as'   => 'jobDetail'
    ]); 

     // Get Exibitor Detail
    Route::get('front/exibitor/detail/{company_id}',[
        'uses' => 'CompanyController@exibitorDetail',
        'as'   => 'exibitorDetail'
    ]);

   Route::get('/marketing/{fairname}/{channel}',      [
    'uses'    => 'MarketingChannelController@channelClicks',  
    'as'      => 'channelClicks'
   ]);

   Route::get('/email/fair-live-notification/{fair_id}',      [
    'uses'    => 'FairController@fairLiveNotification',  
    'as'      => 'fairLiveNotification'
   ]);

   Route::get('/email/fair-end-notification/{fair_id}',      [
    'uses'    => 'FairController@fairEndNotification',  
    'as'      => 'fairEndNotification'
   ]);

   Route::get('/email/webinar-live-notification/{webinar_id}',      [
    'uses'    => 'WebinarController@webinarLiveNotification',  
    'as'      => 'webinarLiveNotification'
   ]);

   Route::get('/email/unsubscribe/{fair_id}/{candidate_id}',      [
    'uses'    => 'CandidateController@Unsubscribe',  
    'as'      => 'Unsubscribe'
   ]);   
});



