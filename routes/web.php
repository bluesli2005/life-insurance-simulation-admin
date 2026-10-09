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

Route::middleware('guest')->group(function () {
    Route::get('/login', 'Auth\\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'Auth\\LoginController@login');
    Route::get('/register', 'Auth\\RegisterController@showRegistrationForm')->name('register');
    Route::post('/register', 'Auth\\RegisterController@register')->middleware('throttle:6,1');
    Route::get('/password/reset', 'Auth\\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('/password/email', 'Auth\\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('/password/reset/{token}', 'Auth\\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('/password/reset', 'Auth\\ResetPasswordController@reset')->name('password.update');
});

Route::post('/logout', 'Auth\\LoginController@logout')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/password/confirm', 'Auth\\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
    Route::post('/password/confirm', 'Auth\\ConfirmPasswordController@confirm');
    Route::post('/admin/password', 'Auth\\ChangePasswordController@update')->name('admin.password.update');

    Route::prefix('admin/api/v1')->as('admin.api.v1.')->group(function () {
        Route::get('session', 'Api\\AdminSessionController@show');
        Route::apiResource('simulation-applications', 'Api\\SimulationApplicationController')->only(['index', 'show']);
        Route::middleware('can:applications.write')->group(function () {
            Route::apiResource('simulation-applications', 'Api\\SimulationApplicationController')->only(['store', 'update']);
        });
        Route::delete('simulation-applications/{simulation_application}', 'Api\\SimulationApplicationController@destroy')
            ->middleware('can:applications.delete');
        Route::middleware('can:users.manage')->group(function () {
            Route::get('users', 'Api\\UserRoleController@index');
            Route::patch('users/{user}/role', 'Api\\UserRoleController@update');
        });
    });

    Route::get('/admin/{path?}', function () {
        return response()->file(resource_path('spa.html'), ['Cache-Control' => 'no-store'])->setPrivate();
    })->where('path', '.*');
});
