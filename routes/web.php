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

// TODO Change permissions and middleware

Route::get('/', 'Controller@getIndex');

/**
 * Calendar routes
 */
Route::post('bookings/{id}/book', 'EntityController@postBook')->middleware('auth');
Route::get ('bookings/{id}/book', 'EntityController@getBook')->middleware('auth');
Route::get ('bookings/{id}/ical', 'EntityController@getIcal');
Route::get ('bookings/{id}/{year?}/{week?}/{highlight?}', 'EntityController@getShow');

Route::get ('events/{id}', 'EventController@getShow');
Route::get ('events/{id}/edit', 'EventController@getEdit')->middleware('auth')->middleware('isUserOrAdminForEvent');
Route::post('events/{id}/edit', 'EventController@postEdit')->middleware('auth')->middleware('isUserOrAdminForEvent');
Route::get ('events/{id}/delete', 'EventController@getDelete')->middleware('auth')->middleware('isUserOrAdminForEvent');
Route::post('events/{id}/delete', 'EventController@postDelete')->middleware('auth')->middleware('isUserOrAdminForEvent');

/**
 * Authentication routes
 */
Route::get ('logout', 'AuthController@getLogout')->middleware('auth');
Route::get ('login', 'AuthController@getLogin')->middleware('guest');
Route::get ('login-complete/{token}', 'AuthController@getLoginComplete')->middleware('guest');

/**
 * Admin routes.
 */
Route::get ('admin', 'Admin\AdminController@getIndex')->middleware('auth')->middleware('isSomeAdmin');
Route::get ('admin/bookings', 'Admin\BookingAdminController@getShow')->middleware('auth')->middleware('isSomeAdmin');
Route::post('admin/bookings', 'Admin\BookingAdminController@postShow')->middleware('auth')->middleware('isSomeAdmin');
Route::get ('admin/bookings/{id}/accept', 'Admin\BookingAdminController@getAccept')->middleware('auth')->middleware('isAdminForEvent');
Route::get ('admin/bookings/{id}/decline', 'Admin\BookingAdminController@getDecline')->middleware('auth')->middleware('isAdminForEvent');

Route::get ('admin/entities', 'Admin\EntityAdminController@getShow')->middleware('auth')->middleware('isSomeAdmin');
Route::get ('admin/entities/new', 'Admin\EntityAdminController@getNew')->middleware('auth')->middleware('admin');
Route::post('admin/entities/new', 'Admin\EntityAdminController@postNew')->middleware('auth')->middleware('admin');
Route::get ('admin/entities/edit/{id}', 'Admin\EntityAdminController@getEdit')->middleware('auth')->middleware('admin');
Route::post('admin/entities/edit/{id}', 'Admin\EntityAdminController@postEdit')->middleware('auth')->middleware('admin');

Route::get ('admin/import', 'Admin\ImportAdminController@getIndex')->middleware('auth')->middleware('admin');
Route::post('admin/import', 'Admin\ImportAdminController@postIndex')->middleware('auth')->middleware('admin');

Route::get ('js/cors/{file}', function($file) { 
	return response(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/js/' . $file))
		->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', '*');
});