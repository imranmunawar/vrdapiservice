<?php
use Illuminate\Http\Request;
Route::group(['prefix' => 'auth','namespace' => 'Api\V1'], function () {
    Route::post('login', 'AuthController@login');
    //Check if user email is already exist
    Route::post('/check-user-email', 'UserController@checkUserEmail')->name('IsUserEmailExist');
    Route::post('signup', 'AuthController@signup');
  
    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });

    /* Candidates Routes */
    Route::get('candidate/show/{id}',       ['uses' => 'CandidateController@show',   'as'  => 'showCandidate']);
    Route::get('candidates/list/{fair_id}', ['uses' => 'CandidateController@index',  'as'  => 'listCandidates']);
    Route::post('candidates',               ['uses' => 'CandidateController@store',  'as'  => 'createCandidate']);
    Route::get('candidates/edit/{user}',    ['uses' => 'CandidateController@edit',   'as'  => 'editCandidate']);
    Route::patch('candidates/update/{id}',  ['uses' => 'CandidateController@update', 'as'  => 'updateCandidate']);
    Route::delete('candidates/delete/{id}', ['uses' => 'CandidateController@destroy','as'  => 'deleteCandidate']);

    /* Show Fair Info Using Short Name */
     Route::post('fair/show-by-shortname/',      ['uses' => 'FairController@showFairByShortname',   'as'  => 'showFairByShortname']);

});
Route::group(['namespace' => 'Auth','prefix' => 'password'], function () {    
    Route::post('create', 'ResetPasswordController@create');
    Route::get('find/{token}', 'ResetPasswordController@find');
    Route::post('reset', 'ResetPasswordController@reset');
});

Route::group(['namespace' => 'Api\V1','middleware' => 'auth:api'], function () {

    /* Users Crud Routes */
    Route::get('users/show/{id}',      ['uses' => 'UserController@show',   'as'  => 'showUser']);
    Route::get('users/list/{type}/{company_id?}',    ['uses' => 'UserController@index',  'as'  => 'listUsers']);
    Route::post('users',               ['uses' => 'UserController@store',  'as'  => 'createUser']);
    Route::get('users/edit/{user}',    ['uses' => 'UserController@edit',   'as'  => 'editUser']);
    Route::patch('users/update/{id}',  ['uses' => 'UserController@update', 'as'  => 'updateUser']);
    Route::delete('users/delete/{id}', ['uses' => 'UserController@destroy','as'  => 'deleteUser']);

     /* Company Crud Routes */
    Route::get('company/show/{id}',        ['uses' => 'CompanyController@show',   'as'  => 'showCompany']);
    Route::get('companies/list/{fair_id?}',['uses' => 'CompanyController@index',  'as'  => 'listCompanies']);
    Route::post('companies',               ['uses' => 'CompanyController@store',  'as'  => 'createCompany']);
    Route::get('company/edit/{id}',        ['uses' => 'CompanyController@edit',   'as'  => 'editCompany']);
    Route::patch('company/update/{id}',    ['uses' => 'CompanyController@update', 'as'  => 'updateCompany']);
    Route::delete('company/delete/{id}',   ['uses' => 'CompanyController@destroy','as'  => 'deleteCompany']);

      /* Fair Media Crud Routes */
    Route::get('company/media/show/{id}',      ['uses' => 'CompanyMediaController@show',    'as'  => 'showCompanyMedia']);
    Route::get('company/media/list/{fair_id}', ['uses' => 'CompanyMediaController@index',   'as'  => 'listCompanyMedia']);
    Route::post('company/media',               ['uses' => 'CompanyMediaController@store',   'as'  => 'createCompanyMedia']);
    Route::get('company/media/{id}',           ['uses' => 'CompanyMediaController@edit',    'as'  => 'editCompanyMedia']);
    Route::patch('company/media/update/{id}',  ['uses' => 'CompanyMediaController@update',  'as'  => 'updateCompanyMedia']);


    /* Company Jobs Crud Routes */
    Route::get('job/show/{id}',        ['uses' => 'CompanyJobController@show',   'as'  => 'showJob']);
    Route::get('jobs/list/{company_id?}/{fair_id?}',['uses' => 'CompanyJobController@index',  'as'  => 'listJobs']);
    Route::post('jobs',               ['uses' => 'CompanyJobController@store',  'as'  => 'createCompanyJob']);
    Route::get('job/edit/{id}',        ['uses' => 'CompanyJobController@edit',   'as'  => 'editCompanyJob']);
    Route::patch('job/update/{id}',    ['uses' => 'CompanyJobController@update', 'as'  => 'updateCompanyJob']);
    Route::delete('job/delete/{id}',   ['uses' => 'CompanyJobController@destroy','as'  => 'deleteCompanyJob']);


    Route::get('users/{type}',        ['uses' => 'UserController@getUsersByRole', 'as'  => 'getUsersByRole']);
    /* Fair Crud Routes */
    Route::get('fair/show/{id}',      ['uses' => 'FairController@show',   'as'  => 'showFair']);
    Route::get('fairs/list',          ['uses' => 'FairController@index',  'as'  => 'listFairs']);
    Route::post('fairs',              ['uses' => 'FairController@store',  'as'  => 'createFair']);
    Route::get('fair/edit/{id}',      ['uses' => 'FairController@edit',   'as'  => 'editFair']);
    Route::patch('fair/update/{id}',  ['uses' => 'FairController@update', 'as'  => 'updateFair']);
    Route::delete('fair/delete/{id}', ['uses' => 'FairController@destroy','as'  => 'deleteFair']);
     /* Fair Media Crud Routes */
    Route::get('fair/media/show/{id}',      ['uses' => 'FairMediaController@show',    'as'  => 'showFair']);
    Route::get('fair/media/list/{fair_id}', ['uses' => 'FairMediaController@index',   'as'  => 'listFairs']);
    Route::post('fair/media',               ['uses' => 'FairMediaController@store',   'as'  => 'createFair']);
    Route::get('fair/media/{id}',           ['uses' => 'FairMediaController@edit',    'as'  => 'editFair']);
    Route::patch('fair/media/update/{id}',  ['uses' => 'FairMediaController@update',  'as'  => 'updateFair']);
    Route::delete('fair/media/delete/{id}', ['uses' => 'FairMediaController@destroy', 'as'  => 'deleteFair']);


    Route::get('fair/marketing/channel/list/{fair_id}',      ['uses'    => 'MarketingChannelController@index',  'as'  => 'marketingChannel']);
    Route::post('fair/marketing/channel',          ['uses'    => 'MarketingChannelController@store',  'as'  => 'createMarketingChannel']);
    Route::get('fair/marketing/channel/{id}',      ['uses'    => 'MarketingChannelController@edit',   'as'  => 'editMarketingChannel']);
    Route::patch('fair/marketing/channel/{id}',    ['uses'    => 'MarketingChannelController@update', 'as'  => 'updateMarketingChannel']);
    Route::delete('fair/marketing/channel/{id}',   ['uses'    => 'MarketingChannelController@destroy','as'  => 'deleteMarketingChannel']);

    Route::post('fair/main/hall/save',          ['uses'    => 'FairMainHallController@store',  'as'  => 'createMainHall']);
    Route::post('fair/company/stand/{company_id}',          ['uses'    => 'FairMainHallController@companyStand',  'as'  => 'companyStand']);


    Route::get('fair/career/test/list/{fair_id}',      ['uses'    => 'CareerTestController@index',  'as'  => 'careerTest']);
    Route::post('fair/career/test',          ['uses'    => 'CareerTestController@store',  'as'  => 'createCareerTest']);
    Route::get('fair/career/test/{id}',      ['uses'    => 'CareerTestController@edit',   'as'  => 'editCareerTest']);
    Route::patch('fair/career/test/{id}',    ['uses'    => 'CareerTestController@update', 'as'  => 'updateCareerTest']);
    Route::delete('fair/career/test/{id}',   ['uses'    => 'CareerTestController@destroy','as'  => 'deleteCareerTest']);

    Route::get('career/test/answer/list/{test_id}',      ['uses'    => 'CareerTestAnswerController@index',  'as'  => 'careerTestAnswer']);
    Route::post('career/test/answer',          ['uses'    => 'CareerTestAnswerController@store',  'as'  => 'createTestAnswer']);
    Route::get('career/test/answer/{id}',      ['uses'    => 'CareerTestAnswerController@edit',   'as'  => 'editTestAnswer']);
    Route::patch('career/test/answer/{id}',    ['uses'    => 'CareerTestAnswerController@update', 'as'  => 'updateTestAnswer']);
    Route::delete('career/test/answer/{id}',   ['uses'    => 'CareerTestAnswerController@destroy','as'  => 'deleteTestAnswer']);

     Route::get('job/questionnaire/criteria/{fair_id}/{job_id}', ['uses' => 'JobQuestionnaireController@index','as'  => 'JobQuestionnaire']);
      Route::post('job/questionnaire/criteria/set/{job_id}', ['uses' => 'JobQuestionnaireController@store','as'  => 'JobQuestionnaire']);


    Route::get('admin/stats', ['uses' => 'StatsController@index', 'as'  => 'adminStats']);

    /*-------------------- Candidates Front Routes ----------------------*/
    Route::get('career-test/{fair_id}',  ['uses' =>'CandidateController@getCareerTestList', 'as'  => 'getCareerTestList']);
    Route::post('save/candidate/career-test',  ['uses' =>'CandidateController@storeCareerTest', 'as'  => 'storeCareerTest']);
    Route::get('candidate/show/{id}',  ['uses' =>'CandidateController@show', 'as'  => 'storeCareerTest']);
    // Route::get('candidate/career-test/{fair_id}/{candidate_id}',  ['uses' =>'CandidateController@show']);
});