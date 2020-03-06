<?php
use Illuminate\Http\Request;

Route::namespace('Api\V1')->group(function () {
    Route::get('/candidate/download/cv/{candidate_id}',[
      'uses' => 'CandidateController@downloadCV',
      'as'   => 'downloadCandidateCV'
    ]);

    Route::post('candidate/recruiter/chat', ['uses' => 'MessageController@candidateRecruiterChat','as'  => 'candidateRecruiterChat']);

    Route::post('recruiter/send/message', ['uses' => 'MessageController@recruiterSendMessage','as'  => 'recruiterSendMessage']);
});

Route::group(['prefix' => 'auth','namespace' => 'Api\V1'], function () {
    Route::post('front/login', 'AuthController@frontLogin');
    Route::post('backend/login', 'AuthController@backendLogin');
    //Check if user email is already exist
    Route::post('/check-user-email', 'UserController@checkUserEmail')->name('IsUserEmailExist');
    Route::post('signup', 'AuthController@signup');

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });

    Route::get('/fair/candidates/statistics/{fair_id}',[
        'as'   => 'candidateStatistics',
        'uses' => 'CandidateController@candidateStatistics'
    ]);

    /* Candidates Routes */
    Route::get('candidate/show/{id}',[
        'uses' => 'CandidateController@show',
        'as'   => 'showCandidate'
    ]);
    // Candidates List Agints Fair
    Route::get('candidates/list/{fair_id}',[
        'uses' => 'CandidateController@index',
        'as'   => 'listCandidates'
    ]);
    // Register New Caniddate
    Route::post('candidates',[
        'uses' => 'CandidateController@store',
        'as'   => 'createCandidate'
    ]);
    // Get Candidate
    Route::get('candidates/edit/{user}',[
        'uses' => 'CandidateController@edit',
        'as'   => 'editCandidate'
    ]);
    // Update Candidate
    Route::patch('candidates/update/{id}',[
        'uses' => 'CandidateController@update',
        'as'   => 'updateCandidate'
    ]);
    // Delete Candiate
    Route::delete('candidates/delete/{id}',[
        'uses' => 'CandidateController@destroy',
        'as'   => 'deleteCandidate'
    ]);
    // Fair Candidates
    Route::get('/fair/candidates/{fair_id}',[
        'uses' => 'FairController@registeredCandidates',
        'as'   => 'registeredCandidates'
    ]);
    // Fair Filter Candidates
    Route::post('/fair/filter/candidates',[
        'uses' => 'FairController@registeredFilterCandidates',
        'as'   => 'registeredFilterCandidates'
    ]);
    Route::get('/test/route',[
        'uses' => 'FairController@testRoute',
        'as'   => 'testRoute'
    ]);
    // Candidate Deltail
    Route::post('/fair/candidate/detail',[
        'uses' => 'CandidateController@personalAgenda',
        'as'   => 'candidatePersonalAgenda'
    ]);

    /* Show Fair Info Using Short Name */
    Route::post('fair/show-by-shortname/',[
        'uses' => 'FairController@showFairByShortname',
        'as'   => 'showFairByShortname'
    ]);
     /* About Fair */
    Route::get('about/fair/{organizer_id}',[
        'uses' => 'FairController@aboutFair',
        'as'   => 'aboutFair'
    ]);

    // Get Fair Terms And Condition
    Route::get('fair/terms/{fair_id}',[
        'uses' => 'FairController@terms',
        'as'   => 'fairTermsAndCondition'
    ]);

    // Get Fair Privacy Policy
    Route::get('fair/privacy/{fair_id}',[
        'uses' => 'FairController@privacy',
        'as'   => 'fairPrivacy'
    ]);

     // Get Fair Privacy Policy
    Route::get('fair/exhibitors/{fair_id}',[
        'uses' => 'FairController@exhibitors',
        'as'   => 'fairExhibitors'
    ]);

     // Get Fair Jobs
    Route::get('fair/jobs/{fair_id}',[
        'uses' => 'FairController@jobs',
        'as'   => 'fairJobs'
    ]);

     // Get Fair Jobs
    Route::get('job/detail/{job_id}/{candidate_id?}',[
        'uses' => 'CompanyJobController@detail',
        'as'   => 'jobDetail'
    ]);

    // Get Jobs Matching
    Route::get('fair/jobs/matching/{fair_id}/{candidate_id}/{job_id}',[
       'uses' => 'FairController@jobsMatching',
       'as'   => 'fairJobsMatch'
    ]);
});

Route::group(['namespace' => 'Auth','prefix' => 'password'], function () {
    Route::post('create', 'ResetPasswordController@create');
    Route::get('find/{token}', 'ResetPasswordController@find');
    Route::post('reset', 'ResetPasswordController@reset');
});

Route::group(['namespace' => 'Api\V1','middleware' => 'auth:api'], function () {

    /* Users Crud Routes */
    Route::get('users/show/{id}',[
        'uses' => 'UserController@show',
        'as'   => 'showUser'
    ]);
    // List Users
    Route::get('users/list/{type}/{company_id?}',[
        'uses' => 'UserController@index',
        'as'   => 'listUsers'
    ]);
    // Create user
    Route::post('users',[
        'uses' => 'UserController@store',
        'as'   => 'createUser'
    ]);
    Route::get('users/edit/{user}',[
        'uses' => 'UserController@edit',
        'as'   => 'editUser'
    ]);
    Route::patch('users/update/{id}',[
        'uses' => 'UserController@update',
        'as'   => 'updateUser'
    ]);
    Route::delete('users/delete/{id}',[
        'uses' => 'UserController@destroy',
        'as'   => 'deleteUser'
    ]);

     /* Company Crud Routes */
    Route::get('company/show/{id}',[
        'uses' => 'CompanyController@show',
        'as'   => 'showCompany'
    ]);
    Route::get('companies/list/{fair_id?}',[
        'uses' => 'CompanyController@index',
        'as'   => 'listCompanies'
    ]);
    Route::post('companies',[
        'uses' => 'CompanyController@store',
        'as'   => 'createCompany'
    ]);
    Route::get('company/edit/{id}',[
        'uses' => 'CompanyController@edit',
        'as'   => 'editCompany'
    ]);
    Route::patch('company/update/{id}',[
        'uses' => 'CompanyController@update',
        'as'   => 'updateCompany'
    ]);
    Route::delete('company/delete/{id}',[
        'uses' => 'CompanyController@destroy',
        'as'   => 'deleteCompany'
    ]);

      /* Fair Media Crud Routes */
    Route::get('company/media/show/{id}',[
        'uses' => 'CompanyMediaController@show',
        'as'   => 'showCompanyMedia'
    ]);
    Route::get('company/media/list/{fair_id}',[
        'uses' => 'CompanyMediaController@index',
        'as'   => 'listCompanyMedia'
    ]);
    Route::post('company/media',[
        'uses' => 'CompanyMediaController@store',
        'as'   => 'createCompanyMedia'
    ]);
    Route::get('company/media/{id}',[
        'uses' => 'CompanyMediaController@edit',
        'as'   => 'editCompanyMedia'
    ]);
    Route::patch('company/media/update/{id}',[
        'uses' => 'CompanyMediaController@update',
        'as'   => 'updateCompanyMedia'
    ]);


    /* Company Jobs Crud Routes */
    Route::get('job/show/{id}',[
        'uses' => 'CompanyJobController@show',
        'as'   => 'showJob'
    ]);
    Route::get('jobs/list/{company_id?}/{recruiter_id?}',[
        'uses' => 'CompanyJobController@index',
        'as'   => 'listJobs'
    ]);
    Route::post('jobs',[
        'uses' => 'CompanyJobController@store',
        'as'   => 'createCompanyJob'
    ]);
    Route::get('job/edit/{id}',[
        'uses' => 'CompanyJobController@edit',
        'as'   => 'editCompanyJob'
    ]);
    Route::patch('job/update/{id}',[
        'uses' => 'CompanyJobController@update',
        'as'   => 'updateCompanyJob'
    ]);
    Route::delete('job/delete/{id}',[
        'uses' => 'CompanyJobController@destroy',
        'as'   => 'deleteCompanyJob'
    ]);

    /* Company Jobs Crud Routes */
    Route::get('webinars/list/{company_id}',[
        'uses' => 'WebinarController@index',
        'as'   => 'webinars'
    ]);
    Route::post('webinars',[
        'uses' => 'WebinarController@store',
        'as'   => 'createWebinar'
    ]);
    Route::get('webinar/edit/{id}',[
        'uses' => 'WebinarController@edit',
        'as'   => 'editWebinar'
    ]);
    Route::patch('webinar/update/{id}',[
        'uses' => 'WebinarController@update',
        'as'   => 'updateWebinar'
    ]);
    Route::delete('webinar/delete/{id}',[
        'uses' => 'WebinarController@destroy',
        'as'   => 'deleteWebinar'
    ]);

    Route::get('users/{type}',        [
        'uses' => 'UserController@getUsersByRole',
        'as'   => 'getUsersByRole']);
    /* Fair Crud Routes */
    Route::get('fair/show/{id}',[
        'uses' => 'FairController@show',
        'as'   => 'showFair'
    ]);
    Route::get('fairs/list',[
        'uses' => 'FairController@index',
        'as'   => 'listFairs'
    ]);
    Route::post('fairs',[
        'uses' => 'FairController@store',
        'as'   => 'createFair'
    ]);
    Route::get('fair/edit/{id}',[
        'uses' => 'FairController@edit',
        'as'   => 'editFair'
    ]);
    Route::patch('fair/update/{id}',[
        'uses' => 'FairController@update',
        'as'   => 'updateFair'
    ]);
    Route::delete('fair/delete/{id}',[
        'uses' => 'FairController@destroy',
        'as'   => 'deleteFair'
    ]);

    Route::get('fair/job/applications/{job_id}',[
        'uses' => 'CompanyJobController@jobApplications',
        'as'   => 'jobApplications'
    ]);

    Route::get('/fair/candidate/block/{candidate_id}/{fair_id}',[
      'uses' => 'CandidateController@blockCandidate',
      'as'   => 'blockCandidate'
    ]);

    Route::get('/fair/candidate/unblock/{candidate_id}/{fair_id}',[
      'uses' => 'CandidateController@unBlockCandidate',
      'as'   => 'unBlockCandidate'
    ]);

    Route::get('/fair/candidate/delete/{candidate_id}/{fair_id}',[
      'uses' => 'CandidateController@deleteCandidate',
      'as'   => 'deleteCandidate'
    ]);

    Route::post('/fair/candidates/bluk/block',[
      'uses' => 'CandidateController@blukBlockCandidates',
      'as'   => 'blukBlockCandidates'
    ]);

    Route::get('/fair/candidate/reset/password/{candidate_id}',[
      'uses' => 'CandidateController@resetCandidatePassword',
      'as'   => 'resetCandidatePassword'
    ]);

    Route::post('set/recruiter/status',[
        'uses' => 'UserController@setRecruiterStatus',
        'as'   => 'setRecruiterStatus'
    ]);

    Route::post('/fair/recruiter/candidates', 'CandidateController@recruiterCandidates');
    Route::post('/recruiter/online/candidates', 'CandidateController@recruiterOnlineCandidates');

    Route::post('/recruiter/candidate/agenda/view',[
      'uses' => 'CandidateController@postAgendaView',
      'as'   => 'postAgendaView'
    ]);

    Route::post('candidate/recruiter/action',[
        'uses' => 'CandidateController@recruiterAction',
        'as'   => 'recruiterAction'
    ]);

    Route::post('/fair/chat/transcript',[
        'uses' => 'CandidateController@userChats',
        'as'   => 'userChats'
    ]);

    Route::get('/fair/chat/transcript-details/{one}/{two}/{userid}',[
        'uses' => 'CandidateController@chatConversation',
        'as'   => 'chatConversation'
    ]);

    /* Fair Settings */
    Route::get('fair/setting/show/{fair_id}',[
        'uses' => 'FairSettingController@show',
        'as'   => 'showFairSetting'
    ]);
    Route::post('fair/setting',[
        'uses' => 'FairSettingController@store',
        'as'   => 'createFairSetting'
    ]);

     /* Fair Media Crud Routes */
    Route::get('fair/media/show/{id}',[
        'uses' => 'FairMediaController@show',
        'as'   => 'showFair'
    ]);
    Route::get('fair/media/list/{fair_id}',[
        'uses' => 'FairMediaController@index',
        'as'   => 'listFairs'
    ]);
    Route::post('fair/media',[
        'uses' => 'FairMediaController@store',
        'as'   => 'createFair'
    ]);
    Route::get('fair/media/{id}',[
        'uses' => 'FairMediaController@edit',
        'as'  => 'editFair'
    ]);
    Route::patch('fair/media/update/{id}',[
        'uses' => 'FairMediaController@update',
        'as'   => 'updateFair'
    ]);
    Route::delete('fair/media/delete/{id}',[
        'uses' => 'FairMediaController@destroy',
        'as'   => 'deleteFair'
    ]);


    Route::get('fair/marketing/channels/stats/{fair_id}',      [
        'uses'    => 'StatsController@marketingStats',
        'as'      => 'marketingStats'
    ]);

    Route::get('fair/marketing/channel/list/{fair_id}',      [
        'uses'    => 'MarketingChannelController@index',
        'as'      => 'marketingChannel'
    ]);
    Route::post('fair/marketing/channel',[
        'uses'    => 'MarketingChannelController@store',
        'as'      => 'createMarketingChannel'
    ]);
    Route::get('fair/marketing/channel/{id}',[
        'uses'    => 'MarketingChannelController@edit',
        'as'      => 'editMarketingChannel'
    ]);
    Route::patch('fair/marketing/channel/{id}',[
        'uses'    => 'MarketingChannelController@update',
        'as'      => 'updateMarketingChannel'
    ]);
    Route::delete('fair/marketing/channel/{id}',[
        'uses'    => 'MarketingChannelController@destroy',
        'as'      => 'deleteMarketingChannel'
    ]);

    Route::get('/fair/marketing-channels/candidates/{fair_id}/{channel_id}',[
      'uses' => 'MarketingChannelController@channelRegisteredCandidates',
      'as'   => 'channelRegisteredCandidates'
    ]);

    Route::post('fair/main/hall/save',[
        'uses'    => 'FairMainHallController@store',
        'as'      => 'createMainHall'
    ]);
    Route::post('fair/mian/hall/set/width',[
        'uses' => 'FairMainHallController@createStandWidth',
        'as'   => 'createStandWidth'
    ]);
    Route::post('fair/company/stand/{company_id}',[
        'uses' => 'FairMainHallController@companyStand',
        'as'   => 'companyStand'
    ]);

    Route::get('fair/career/test/list/{fair_id}',[
        'uses' => 'CareerTestController@index',
        'as'   => 'careerTest'
    ]);
    Route::post('fair/career/test',[
        'uses' => 'CareerTestController@store',
        'as'   => 'createCareerTest'
    ]);
    Route::get('fair/career/test/{fair_id}',[
        'uses'  => 'CareerTestController@edit',
        'as'    => 'editCareerTest'
    ]);
    Route::get('fair/career/test/show/{fair_id}',[
        'uses' => 'CareerTestController@show',
        'as'   => 'showCareerTest'
    ]);
    Route::patch('fair/career/test/{id}',[
        'uses' => 'CareerTestController@update',
        'as'   => 'updateCareerTest'
    ]);
    Route::delete('fair/career/test/{id}',[
        'uses' => 'CareerTestController@destroy',
        'as'   => 'deleteCareerTest'
    ]);

    Route::get('career/test/answer/list/{test_id}',[
        'uses' => 'CareerTestAnswerController@index',
        'as'   => 'careerTestAnswer'
    ]);
    Route::post('career/test/answer',[
        'uses' => 'CareerTestAnswerController@store',
        'as'   => 'createTestAnswer'
    ]);
    Route::get('career/test/answer/{id}',[
        'uses' => 'CareerTestAnswerController@edit',
        'as'   => 'editTestAnswer'
    ]);
    Route::patch('career/test/answer/{id}',[
        'uses' => 'CareerTestAnswerController@update',
        'as'   => 'updateTestAnswer'
    ]);
    Route::delete('career/test/answer/{id}',[
        'uses' => 'CareerTestAnswerController@destroy',
        'as'   => 'deleteTestAnswer'
    ]);

    Route::get('job/questionnaire/criteria/{fair_id}/{job_id}',[
        'uses' => 'JobQuestionnaireController@index',
        'as'   => 'JobQuestionnaire'
    ]);
    Route::post('job/questionnaire/criteria/set/{job_id}',[
        'uses' => 'JobQuestionnaireController@store',
        'as'   => 'JobQuestionnaire'
    ]);


    Route::get('recruiter/questionnaire/criteria/{fair_id}/{recruiter_id}',[
        'uses' => 'RecruiterQuestionnaireController@index',
        'as'   => 'recruiterQuestionnaire'
    ]);
    Route::post('recruiter/questionnaire/criteria/set',[
        'uses' => 'RecruiterQuestionnaireController@store',
        'as'   => 'setRecruiterQuestionnaire'
    ]);

    Route::get('webinar/questionnaire/criteria/{fair_id}/{webinar_id}',[
        'uses' => 'WebinarQuestionnaireController@index',
        'as'   => 'webinarQuestionnaire'
    ]);
    Route::post('webinar/questionnaire/criteria/set',[
        'uses' => 'WebinarQuestionnaireController@store',
        'as'   => 'setWebinarQuestionnaire'
    ]);



    /* -------------- Dashboard Stats Routes --------------------- */

    Route::get('admin/stats',[
        'uses' => 'StatsController@index',
        'as'   => 'adminStats'
    ]);

    Route::get('organizer/stats/{id}',[
        'uses' => 'StatsController@organizerStats',
        'as'   => 'organizerStats'
    ]);

    Route::get('fair/stats/{fair_id}',[
        'uses' => 'StatsController@fairStats',
        'as'   => 'fairStats'
    ]);


    Route::get('recruiter/stats/{recruiter_id}/{fair_id}',[
        'uses' => 'StatsController@recruiterStats',
        'as'   => 'recruiterStats'
    ]);


    Route::get('company/stats/{company_id}',[
        'uses' => 'StatsController@companyStats',
        'as'   => 'companyStats'
    ]);

    Route::get('company/listing',[
        'uses' => 'StatsController@companyListing',
        'as'   => 'companyListing'
    ]);

    Route::get('job/listing',[
        'uses' => 'StatsController@jobListing',
        'as'   => 'jobListing'
    ]);

    Route::get('candidate/listing',[
        'uses' => 'UserController@candidateListing',
        'as'   => 'candidateListing'
    ]);

    /*-------------------- VRD Front Routes ----------------------*/

    // Get Candidate Career Test
    Route::get('canidate/career-test/{canidate_id}/{fair_id}',[
        'uses' =>'CandidateController@getCareerTestList',
        'as'   => 'getCareerTestList'
    ]);
    // Save Candidate Career Test
    Route::post('save/candidate/career-test',[
        'uses' =>'CandidateController@storeCareerTest',
        'as'   => 'storeCareerTest'
    ]);
    // Save Candidate Profile Image
    Route::post('save/candidate/profile-image',[
        'uses' =>'CandidateController@upProfileImage',
        'as'   => 'upProfileImage'
    ]);
     // Save Candidate Resume
    Route::post('save/candidate/resume',[
        'uses' =>'CandidateController@updateResume',
        'as'   => 'upResume'
    ]);
    // Get Specific Candidate
    Route::get('candidate/show/{id}',[
        'uses' =>'CandidateController@show',
        'as'   => 'storeCareerTest'
    ]);
    // Get Candidate Matching Jobs
    Route::post('candidate/jobs',[
        'uses' =>'CandidateController@getMatchingJobs',
        'as'   => 'getMatchingJobs'
    ]);
    // Get Candidate Matching Webinars
    Route::post('candidate/webinars',[
        'uses' =>'CandidateController@getMatchingWebinars',
        'as'   => 'getMatchingWebinars'
    ]);
    // Candidate Apply Job
    Route::post('candidate/apply/job',[
        'uses' =>'CandidateController@applyJob',
        'as'   => 'candidateApplyJob'
    ]);
    // Candidate Added Webinar
    Route::post('candidate/add/webinar',[
        'uses' =>'CandidateController@addWebinar',
        'as'   => 'candidateaddWebinar'
    ]);
    // Candidate Update Profile
    Route::post('candidate/update',[
        'uses' =>'CandidateController@update',
        'as'   => 'candidateUpdate'
    ]);
    // Get Candidate Matching Recruiters
    Route::post('candidate/recruiters',[
        'uses' =>'CandidateController@getMatchingRecruiters',
        'as'   => 'getMatchingRecruiters'
    ]);
    // Get Candidate Company Jobs
    Route::post('candidate/company/jobs',[
        'uses' => 'CompanyController@candidateCompanyJobs',
        'as'  => 'candidateCompanyJobs'
    ]);
    // Get Candidate Company Recruiters
    Route::post('candidate/company/recruiters',[
        'uses' => 'CompanyController@candidateCompanyRecruiters',
        'as'   => 'candidateCompanyRecruiters'
    ]);

    // Get Candidate Company Recruiters
    Route::post('candidate/company/webinars',[
        'uses' => 'CompanyController@candidateCompanyWebinars',
        'as'   => 'candidateCompanyWebinars'
    ]);
    // Get Company Detail
    Route::post('company/detail',[
        'uses' => 'CompanyController@companyDetail',
        'as'   => 'companyDetail'
    ]);

     // Candidate In Main Hall
    Route::get('/fair/candidate/inhall/{fair_id}/{candidate_id}',[
        'uses' => 'CandidateController@inHall',
        'as'   => 'candidateinHall'
    ]);

    /* Messages Routes */

    Route::post('messages/list', ['uses' => 'MessageController@index','as'  => 'listMessages']);

    // Route::post('candidate/recruiter/chat', ['uses' => 'MessageController@candidateRecruiterChat','as'  => 'candidateRecruiterChat']);

    // Route::post('recruiter/send/message', ['uses' => 'MessageController@recruiterSendMessage','as'  => 'recruiterSendMessage']);

    // Route::get('candidate/career-test/{fair_id}/{candidate_id}',  ['uses' =>'CandidateController@show']);

    /* Scheduling Routes */
    Route::post('/company/recruiters/scheduling', 'RecruiterSchedulingController@index');
    // Route::get('/company/recruiters/scheduling/data', 'RecruiterSchedulingController@indexData');
    Route::post('/company/recruiters/scheduled/interviews', 'RecruiterSchedulingController@scheduledInterviews');
    Route::get('/company/recruiters/scheduling/add', 'RecruiterSchedulingController@addSchedule');
    Route::post('/company/recruiters/scheduling/create', 'RecruiterSchedulingController@createSchedule');
    Route::get('/company/recruiters/scheduling/edit/{id}', 'RecruiterSchedulingController@editSchedule');
    Route::post('/company/recruiters/scheduling/update', 'RecruiterSchedulingController@updateSchedule');
    Route::delete('/company/recruiters/scheduling/delete/{id}', 'RecruiterSchedulingController@deleteSchedule');
    Route::post('/company/recruiters/scheduling/invite', 'RecruiterSchedulingController@inviteCandidate');
    Route::post('/company/recruiters/interview/approvals', 'RecruiterSchedulingController@interviewApprovals');
    Route::get('/recruiter/approve/interview/{interview_id}', 'RecruiterSchedulingController@interviewApproved');

});


Route::group(['namespace' => 'Api\V1'], function () { 
    /* Candidate Scheduling Routes*/
    Route::get('/candidate/interview/fetch/calender', 'RecruiterSchedulingController@candidateFetchCalender');
    Route::get('/candidate/interview/fetch/schedules', 'RecruiterSchedulingController@candidateFetchSchedules');
    Route::get('/candidate/interview/book/schedules', 'RecruiterSchedulingController@candidateBookSchedules');
    Route::get('/candidate/interview/book/schedules', 'RecruiterSchedulingController@candidateBookSchedules');
    Route::get('/candidate/interview/cancel/{u_id}', 'RecruiterSchedulingController@candidateCancelSchedule');
    Route::get('/interview/invitation/{u_id}', 'RecruiterSchedulingController@invitationLink');
    Route::post('/interview/fetch/schedules', 'RecruiterSchedulingController@fetchSchedules');
    Route::post('/interview/book/schedules', 'RecruiterSchedulingController@bookSchedules');
});