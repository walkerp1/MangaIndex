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

Route::get('/search/image', array('as' => 'searchImage', 'uses' => 'SearchController@image'));
Route::post('/search/image/submit', array('as' => 'searchImageSubmit', 'before' => 'csrf', 'uses' => 'SearchController@imageSubmit'));
Route::get('/search/suggest', array('as' => 'searchSuggest', 'uses' => 'SearchController@suggest'));
Route::get('/search/{keyword?}', array('as' => 'search', 'uses' => 'SearchController@search'));
Route::get('/search/{type?}/{keyword?}', array('as' => 'searchKeywordType', 'uses' => 'SearchController@searchKeywordType'));

Route::get('/recent', array('as' => 'recent', 'uses' => 'RecentController@recent'));
Route::get('/recent/rss', array('as' => 'recentRss', 'uses' => 'RecentController@rss'));

Route::post('/user/notifications/watch', array('uses' => 'UsersController@toggleWatch'));
Route::get('/user/notifications', array('uses' => 'UsersController@notifications'));
Route::post('/user/notifications/dismiss', array('before' => 'csrf', 'uses' => 'UsersController@dismiss'));
Route::get('/user/notifications/download/{notification}/{filename}', array('as' => 'notificationDownload', 'uses' => 'UsersController@downloadDismiss'));

Route::get('/api/muid/{muId}', array('uses' => 'ApiController@muid'));
Route::get('/api/register', array('uses' => 'ApiController@register'));
Route::get('/api/changepassword', array('uses' => 'ApiController@changePassword'));

Route::get('/admin/flushcache', array('uses' => 'AdminController@flushCache'));

Route::post('/path/save', array('before' => 'csrf', 'uses' => 'IndexController@save'));
Route::get('{path}', array('uses' => 'IndexController@index'))->where('path', '^.*');