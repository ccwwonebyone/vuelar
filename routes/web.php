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
//web
Route::get('/','IndexController@index');
Route::get('/database/{id?}','IndexController@index')->name('index');
Route::get('login','LoginController@index')->name('login');
Route::get('install','InstallController@index')->name('install');
//api
Route::group(['prefix'=>'install'],function(){
	Route::get('checkEnv','InstallController@checkEnv');
	Route::post('start','InstallController@start');
	Route::get('getInstallDatabases','InstallController@getInstallDatabases');
});
	//新增数据库
Route::post('insertDbInfo','IndexController@insertDbInfo');
	//获取基本信息
Route::post('getInfo','IndexController@getInfo')->name('getInfo');
	//测试
Route::get('showTabs','DataBaseController@showTabs');
	//更新某张表的某个字段
Route::post('update','IndexController@update');
	//更新数据表
Route::post('updateTable','IndexController@updateTable');
	//增加或删除数据表
Route::post('updateDatabase','IndexController@updateDatabase');
	//更新数据库连接信息
Route::post('updateDatabaseConfig','IndexController@updateDatabaseConfig');
	//删除数据库/表
Route::post('del','IndexController@del');
//验证登陆
Route::post('checkLogin','LoginController@checkLogin');