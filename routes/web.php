<?php
Route::get('/sitemap.xml', 'AppController@sitemap')->name('sitemap');
Route::group([
	'middleware' => ['web'],
	'prefix' => Config::get('route_prefix')
], function () {
	Route::match(['get', 'post'], '/ajax', 'AjaxController');
	Route::get('/', 'AppController@home')->name('home');
	Route::get('/boosters', 'AppController@boosters')->name('boosters');
	Route::get('/services', 'AppController@services')->name('services');
	Route::get('/blacklist', 'AppController@blacklist')->name('blacklist');
	Route::get('/404', 'AppController@error404')->name('404');
	Route::get('/statistics', 'AppController@statistics')->name('statistics');
	Route::get('/statistics/{number}', 'AppController@statisticsOrder')->name('statisticsOrder');
	Route::get('/embed/statistics', 'AppController@statisticsEmbed')->name('statisticsEmbed');
	Route::get('/embed/statistics/{number}', 'AppController@statisticsEmbedOrder')->name('statisticsEmbedOrder');
	Route::post('/statistics', array('middleware' => 'cors', 'uses' => 'AjaxController', 'name' => 'statistics_ajax'));
});
