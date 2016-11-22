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

Route::get('/', 'Controller@getIndex');

/**
 * Calendar routes
 */
Route::post('bookings/{id}/book', 'EntityController@postBook')->middleware('auth');
Route::get ('bookings/{id}/book', 'EntityController@getBook')->middleware('auth');
Route::get ('bookings/{id}/ical', 'EntityController@getIcal');
Route::get ('bookings/{id}/{year?}/{week?}', 'EntityController@getShow');

/**
 * Authentication routes
 */
Route::get ('logout', 'AuthController@getLogout')->middleware('auth');
Route::get ('login', 'AuthController@getLogin')->middleware('guest');
Route::get ('login-complete/{token}', 'AuthController@getLoginComplete')->middleware('guest');

/**
 * Admin routes.
 */
Route::get ('admin', 'Admin\AdminController@getIndex')->middleware('admin');
Route::get ('admin/bookings', 'Admin\BookingAdminController@getShow');
Route::post('admin/bookings', 'Admin\BookingAdminController@postShow');
Route::get ('admin/bookings/{id}/accept', 'Admin\BookingAdminController@getAccept')->middleware('isAdminFor');
Route::get ('admin/bookings/{id}/decline', 'Admin\BookingAdminController@getDecline')->middleware('isAdminFor');

Route::get ('admin/entities', 'Admin\EntityAdminController@getShow');
Route::get ('admin/entities/new', 'Admin\EntityAdminController@getNew');
Route::post('admin/entities/new', 'Admin\EntityAdminController@postNew');
Route::get ('admin/entities/edit/{id}', 'Admin\EntityAdminController@getEdit')->middleware('isAdminFor');
Route::post('admin/entities/edit/{id}', 'Admin\EntityAdminController@postEdit')->middleware('isAdminFor');

Route::get ('admin/import', 'Admin\ImportAdminController@getIndex')->middleware('admin');
Route::post('admin/import', 'Admin\ImportAdminController@postIndex')->middleware('admin');