<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



// Display all SQL executed in Eloquent



Route::post('sign-up', 'UserController@signUp');
Route::any('test', 'UserController@test');

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web', 'influencer']], function () {

    Route::any('auth/google', 'Auth\AuthController@google');
    Route::post('auth/twitter', 'Auth\AuthController@twitter');
    Route::any('auth/instagram', 'Auth\AuthController@instagram');

    Route::post('twitter/ios-verify', 'TwitterController@iosVerify');
    Route::post('youtube/ios-verify', 'YoutubeController@iosVerify');
    Route::post('instagram/ios-verify', 'InstagramController@iosVerify');


    Route::any('unlink', 'Auth\AuthController@unlink');
    Route::get('logout', 'UserController@logout');

    Route::any('twitter/login', ['as' => 'twitter.login',
        'uses' => 'TwitterController@login'
    ]);

    Route::any('twitter/callback', ['as' => 'twitter.callback',
        'uses' => 'TwitterController@callback'
    ]);

    Route::any('youtube/login', ['as' => 'youtube.login',
        'uses' => 'YoutubeController@login'
    ]);

    Route::any('youtube/callback', ['as' => 'youtube.callback',
        'uses' => 'YoutubeController@callback'
    ]);

    Route::any('instagram/login', ['as' => 'instagram.login',
        'uses' => 'InstagramController@login'
    ]);

    Route::any('instagram/callback', ['as' => 'instagram.callback',
        'uses' => 'InstagramController@callback'
    ]);


});




/**
 * Auth user function
 */
Route::group(['middleware' => 'web'], function () {

    Route::get('/', 'HomeController@index');
    Route::get('/csv', 'HomeController@csv');
    //Route::auth();
    Route::get('/home', 'HomeController@index');
    Route::post('login', 'UserController@login');
    Route::get('logout', 'UserController@logout');
    Route::post('edit-profile', 'UserController@editProfile');

    Route::get('get-user', 'UserController@getUser');
    Route::get('del-profile', 'UserController@delProfile');

    Route::get('confirmation-email/{token?}', 'UserController@confirmationEmail');
    Route::get('user-profile/{identity?}', 'UserController@userProfile');

    /**
     * User password API
     */
    Route::post('change-password', 'UserController@changePassword');
    Route::post('reset-password', 'UserController@resetPassword');
    Route::get('forgot-password/{token?}', 'UserController@forgotPassword');
    Route::post('set-new-password/{token?}', 'UserController@setNewPassword');
    Route::get('login-as/{identity}', 'UserController@loginAs');
    Route::any('create-new-password/{token?}', 'UserController@createNewPassword');

    /**
     * Social accounts function
     */
    Route::post('add-social-account', 'AccountController@addSocialAccount');
    Route::post('edit-social-account', 'AccountController@editSocialAccount');
    Route::get('get-social-account-list-by-user', 'AccountController@getSocialAccountListByUser');
    Route::post('view-social-account', 'AccountController@viewSocialAccount');
    Route::post('del-social-account', 'AccountController@delSocialAccount');
    Route::get('get-social-network-list', 'AccountController@getSocialNetworkList');

    /**
     * Brands function
     */

    Route::get('get-brand/{identity?}', 'BrandController@getBrand');
    Route::get('brand-view-campaigns', 'BrandController@brandViewCampaigns');
    Route::post('del-brand', 'BrandController@delBrand');
    Route::get('get-influencers-list', 'BrandController@getInfluencersList');

    Route::post('invite-influencer-to-campaign', 'BrandController@inviteInfluencerToCampaign');
    Route::get('brand-reject-application/{identity?}', 'BrandController@brandRejectApplication');
    Route::get('brand-accept-application/{identity?}', 'BrandController@brandAcceptApplication');

    /**
     * Campaign functions
     */
    Route::get('get-campaign-list', 'CampaignController@getCampaignList');
    Route::post('create-campaign', 'CampaignController@createCampaign');
    Route::post('edit-campaign/{identity?}', 'CampaignController@editCampaign');
    Route::get('get-campaign/{identity?}', 'CampaignController@getCampaign');
    Route::post('del-campaign', 'CampaignController@delCampaign');


    /**
     * Influencers functions
     */
    Route::get('apply-to-campaign/{identity?}', 'InfluencerController@applyToCampaign');
    Route::get('influencer-reject-invited/{identity?}', 'InfluencerController@influencerRejectInvited');
    Route::get('influencer-accept-invited/{identity?}', 'InfluencerController@influencerAcceptInvited');
    Route::get('show-invitation/{identity?}', 'InfluencerController@showInvitation');
    Route::get('show-influencer-campaign/{identity?}', 'InfluencerController@showInfluencerCampaign');
    Route::get('get-influencer/{identity?}', 'InfluencerController@getInfluencer');
    Route::get('show-influencer-campaign-status/{identity?}', 'InfluencerController@showInfluencerCampaignStatus');

    /**
     * Admin functions
     */
    Route::get('/get-user-list', 'AdminController@getUserList');
    Route::get('get-brand-list', 'AdminController@getBrandList');
    Route::post('create-brand', 'AdminController@createBrand');
    Route::any('edit-brand/{identity?}', 'AdminController@editBrand');
    Route::post('create-interest', 'AdminController@createInterest');
    Route::any('edit-interest/{identity?}', 'AdminController@editInterest');
    Route::get('get-interest-list', 'AdminController@getInterestList');
    Route::get('get-interest/{identity?}', 'AdminController@getInterest');
    Route::post('del-interest', 'AdminController@delInterest');

    /**
     * Auxiliary operations
     */
    Route::any('upload', 'HomeController@upload');
    Route::any('get-states-list', 'HomeController@getStatesList');

    /**
     * Post functions
     */

    Route::post('create-post', 'PostController@createPost');
    Route::post('resubmit-post/{identity}', 'PostController@resubmitPost');
    Route::post('brand-accept-post/{identity}', 'PostController@brandAcceptPost');
    Route::post('brand-reject-post/{identity}', 'PostController@brandRejectPost');
    Route::get('post-feeds', 'PostController@postFeeds');
    Route::post('schedule-post/{identity}', 'PostController@schedulePost');
    Route::get('brand-posts-list', 'PostController@brandPostsList');
    Route::get('influencer-post-data/{identity}', 'PostController@influencerPostData');


    /**
     * Version functions
     */
    Route::post('modify-version/{identity}', 'VersionController@modifyVersion');
    Route::get('get-version', 'VersionController@getVersion');


    /**
     * Temporary account
     */
    Route::post('create-temporary-account', 'AdminController@createTemporaryAccount');




});

