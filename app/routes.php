<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::model('notification', 'Notification');

Route::get('/search/suggest', array('as' => 'searchSuggest', 'uses' => 'SearchController@suggest'));
Route::get('/search/{keyword?}', array('as' => 'search', 'uses' => 'SearchController@search'));
Route::get('/search/{type?}/{keyword?}', array('as' => 'searchKeywordType', 'uses' => 'SearchController@searchKeywordType'));

Route::get('/recent', array('as' => 'recent', 'uses' => 'RecentController@recent'));

Route::get('/reports', array('as' => 'reports', 'uses' => 'ReportsController@reports'));
Route::post('/reports/dismiss', array('as' => 'reportDismiss', 'before' => array('csrf'), 'uses' => 'ReportsController@dismiss'));

Route::get('/login', array('as' => 'login', 'uses' => 'UsersController@login'));
Route::get('/logout', array('as' => 'logout', 'uses' => 'UsersController@logout'));

Route::group(array('before' => 'auth'), function() {
    Route::post('/user/notifications/watch', array('uses' => 'UsersController@toggleWatch'));
    Route::get('/user/notifications', array('as' => 'notifications', 'uses' => 'UsersController@notifications'));
    Route::post('/user/notifications/dismiss', array('as' => 'notificationDismiss', 'before' => 'csrf', 'uses' => 'UsersController@dismiss'));
    Route::get('/user/notifications/download/{notification}/{filename}', array('as' => 'notificationDownload', 'uses' => 'UsersController@downloadDismiss'));
});

Route::get('/api/muid/{muId}', array('uses' => 'ApiController@muid'));
Route::get('/api/register', array('uses' => 'ApiController@register'));
Route::get('/api/changepassword', array('uses' => 'ApiController@changePassword'));

Route::get('/admin/flushcache', array('before' => 'auth.super', 'uses' => 'AdminController@flushCache'));

Route::get('/reader/image', array('as' => 'readerImage', 'uses' => 'ReaderController@image'));
Route::get('/reader/{path}', array('as' => 'reader', 'uses' => 'ReaderController@read'));

Route::get('/donate', array('as' => 'donate', 'uses' => function() {
    return View::make('donate', array('pageTitle' => 'Donate'));
}));

Route::post('/path/report', array('before' => 'csrf|auth', 'as' => 'report', 'uses' => 'IndexController@report'));
Route::post('/path/save', array('before' => 'csrf', 'uses' => 'IndexController@save'));
Route::get('/', array('as' => 'home', 'uses' => 'IndexController@index'));
Route::get('{path}', array('uses' => 'IndexController@index'))->where('path', '^.*');