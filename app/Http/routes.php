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

//    Route::get('/', 'HomeController@index');
//    Route::get('/login', 'HomeController@index');
//    Route::get('/logout', 'LoginController@logout');
//    Route::get('/joblist', 'JobController@index');
//    Route::get('/nodelist', 'NodeController@index');
//    Route::post('/discoverd', 'NodeController@discoverd');
//    Route::post('/addsinglenode', 'NodeController@storenode');
//    Route::post('/login', 'LoginController@login');

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

Route::group(['middleware' => ['web']], function () {
    //
    Route::get('/test', 'TestController@test');
    
    Route::get('/', 'HomeController@index');
    Route::get('/login', 'HomeController@index');
    
    Route::get('/logout', 'LoginController@logout');
    Route::post('/login', 'LoginController@login');
    
    Route::get('/joblist', 'JobController@index');
    Route::get('/tasklist', 'JobController@indexTask');
    Route::get('/crtjobviw', 'JobController@createviw');
    Route::get('/checkser', 'JobController@jobExecutorTest');
    Route::get('/progress', 'JobController@getProgress');
    Route::post('/jobexec', 'JobController@jobExecutor');
    Route::post('/jobgonow', 'JobController@jobGoNow');
    Route::get('/viewtask', 'JobController@viewTask');
    Route::get('/killtask', 'JobController@killJobTask');
    Route::get('/prerunjob', 'JobController@createviw');
    Route::get('/removejob', 'JobController@createviw');
    Route::get('/editjob', 'JobController@createviw');
    Route::get('/removeupsh', 'JobController@removeFile');
    Route::get('/getsteplog', 'JobController@readStepLog');
    Route::get('/getpiedata', 'JobController@JobExecPieData');
    Route::get('/getstatesum', 'JobController@JobStatePieData');
    Route::get('/expjobviw', 'JobController@exportJobYaml');
    Route::post('/downjobexpfile', 'JobController@downloadJobYaml');
    Route::post('/saveMyJob', 'JobController@myjobSave');
    Route::post('/uploadsh', 'JobController@getUploadShell');
    
    Route::get('/nodelist', 'NodeController@index');
    Route::get('/getNodeById','NodeController@getNodeById');
    Route::get('/getNodeName','NodeController@getNodeName');
    Route::get('/removenode','NodeController@removeNode');
    Route::post('/discoverd', 'NodeController@discoverd');
    Route::post('/addsinglenode', 'NodeController@storenode');
    Route::post('/importnode', 'NodeController@importNodeYaml');
    Route::get('/exportnode', 'NodeController@exportNodeYaml');
    Route::post('/downexpfile', 'NodeController@downLoadYaml');
                
    Route::get('/configwel','ConfigController@index');
    Route::get('/configDefUser','ConfigController@configuser');
    Route::get('/confighome','ConfigController@home');
    Route::get('/configSSHKey','ConfigController@configSSHKey');
    Route::get('/configSys','ConfigController@configSystem');
    Route::get('/upsysconf','ConfigController@saveSystemConfig');
    Route::post('/resetpass', 'ConfigController@resetpass');
    Route::post('/updatekey', 'ConfigController@updateSSHKey');
    Route::post('/uploadkey', 'ConfigController@getUploadKeyFile');
    
});
