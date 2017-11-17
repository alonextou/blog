<?php

Route::group(['before' => 'auth'], function(){
	Route::group(['prefix' => 'admin'], function()
	{
		Route::get('articles', ['as' => 'admin_articles', 'uses' => 'AdminArticleController@getIndex']);
		Route::get('articles/create', ['as' => 'create_article', 'uses' => 'AdminArticleController@getCreate']);
		Route::get('articles/{alias}', ['as' => 'edit_article', 'uses' => 'AdminArticleController@getUpdate']);
		Route::get('/', ['as' => 'admin', function()
		{
			return View::make('admin/index');
		}]);
		Route::group(['before' => 'csrf'], function(){
			Route::post('articles/create', ['uses' => 'AdminArticleController@postCreate']);
		});
	});
});

Route::group(['before' => 'guest'], function(){
	Route::get('login', ['as' => 'login', 'uses' => 'AuthController@getLogin']);

	Route::group(['before' => 'csrf'], function(){
		Route::post('login', ['uses' => 'AuthController@postLogin']);
	});
});

Route::group(['prefix' => 'blog'], function()
{	
	Route::get('/', ['as' => 'blog', 'uses' => 'BlogController@getIndex']);
	Route::get('/{alias}', ['as' => 'blog_post', 'uses' => 'BlogController@getPost']);
});

Route::get('logout', ['as' => 'logout', 'uses' => 'AuthController@getLogout']);

Route::get('/', ['as' => 'home', 'uses' => 'HomeController@getIndex']);