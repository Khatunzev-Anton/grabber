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

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'grabber'], function() {
    Route::get('grab', ['as' => 'grabber.grab', 'uses' => 'GrabController@Grab']);
    Route::get('googlecheck', ['as' => 'grabber.googlecheck', 'uses' => 'CheckOutGoogleController@CheckGoogle']);
    Route::get('grabgoogle', ['as' => 'grabber.grabgoogle', 'uses' => 'GrabGoogleController@Grab']);
});

