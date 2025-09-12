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

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\EntityAdminController;
use App\Http\Controllers\Admin\ImportAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;

Route::get('/', [Controller::class, 'getIndex']);

/**
 * Calendar routes
 */
Route::post('bookings/{id}/book', [EntityController::class, 'postBook'])->middleware('auth');
Route::get('bookings/{id}/book', [EntityController::class, 'getBook'])->middleware('auth');
Route::get('bookings/{id}/ical', [EntityController::class, 'getIcal']);
Route::get('bookings/{id}/{year?}/{week?}/{highlight?}', [EntityController::class, 'getShow']);

Route::get('events/{id}', [EventController::class, 'getShow']);
Route::get('events/{id}/edit', [EventController::class, 'getEdit'])->middleware('auth')->middleware('isUserOrAdminForEvent');
Route::post('events/{id}/edit', [EventController::class, 'postEdit'])->middleware('auth')->middleware('isUserOrAdminForEvent');
Route::get('events/{id}/delete', [EventController::class, 'getDelete'])->middleware('auth')->middleware('isUserOrAdminForEvent');
Route::post('events/{id}/delete', [EventController::class, 'postDelete'])->middleware('auth')->middleware('isUserOrAdminForEvent');

Route::get('user', [UserController::class, 'getIndex'])->middleware('auth');

/**
 * Authentication routes
 */
Route::get('logout', [AuthController::class, 'getLogout'])->middleware('auth');
Route::get('login', [AuthController::class, 'getLogin'])->middleware('guest')->name('oidc-callback');

/**
 * Admin routes.
 */
Route::get('admin', [AdminController::class, 'getIndex'])->middleware('auth')->middleware('isSomeAdmin');
Route::get('admin/bookings', [BookingAdminController::class, 'getShow'])->middleware('auth')->middleware('isSomeAdmin');
Route::post('admin/bookings', [BookingAdminController::class, 'postShow'])->middleware('auth')->middleware('isSomeAdmin');
Route::get('admin/bookings/{id}/accept', [BookingAdminController::class, 'getAccept'])->middleware('auth')->middleware('isAdminForEvent');
Route::get('admin/bookings/{id}/decline', [BookingAdminController::class, 'getDecline'])->middleware('auth')->middleware('isAdminForEvent');

Route::get('admin/entities', [EntityAdminController::class, 'getShow'])->middleware('auth')->middleware('isSomeAdmin');
Route::get('admin/entities/new', [EntityAdminController::class, 'getNew'])->middleware('auth')->middleware('admin');
Route::post('admin/entities/new', [EntityAdminController::class, 'postNew'])->middleware('auth')->middleware('admin');
Route::get('admin/entities/edit/{id}', [EntityAdminController::class, 'getEdit'])->middleware('auth')->middleware('admin');
Route::post('admin/entities/edit/{id}', [EntityAdminController::class, 'postEdit'])->middleware('auth')->middleware('admin');

Route::get('admin/import', [ImportAdminController::class, 'getIndex'])->middleware('auth')->middleware('admin');
Route::post('admin/import', [ImportAdminController::class, 'postIndex'])->middleware('auth')->middleware('admin');

Route::get('js/cors/{file}', function ($file) {
    return response(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/js/' . $file))
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', '*');
});
