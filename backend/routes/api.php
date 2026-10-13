<?php

Route::prefix('v1')->group(function () {
    Route::get('auth/csrf', function () {
        return response()->noContent();
    });

    Route::middleware('guest')->prefix('auth')->group(function () {
        Route::post('login', 'Auth\LoginController@login');
        Route::post('register', 'Auth\RegisterController@register')->middleware('throttle:6,1');
        Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->middleware('throttle:6,1');
        Route::post('password/reset', 'Auth\ResetPasswordController@reset')->middleware('throttle:6,1');
    });
    Route::post('auth/logout', 'Auth\LoginController@logout');

    // 認証に加え、書き込み・削除・ユーザー管理には個別の権限を確認する。
    Route::middleware('auth')->group(function () {
        Route::get('auth/session', 'Api\AdminSessionController@show');
        Route::post('auth/password/confirm', 'Auth\ConfirmPasswordController@confirm');
        Route::post('auth/password/change', 'Auth\ChangePasswordController@update');
        Route::apiResource('simulation-applications', 'Api\SimulationApplicationController')->only(['index', 'show']);
        Route::get('simulation-applications/{id}/history', 'Api\SimulationApplicationController@history')
            ->middleware('can:applications.delete');
        Route::middleware('can:applications.write')->group(function () {
            Route::apiResource('simulation-applications', 'Api\SimulationApplicationController')->only(['store', 'update']);
        });
        Route::delete('simulation-applications/{simulation_application}', 'Api\SimulationApplicationController@destroy')
            ->middleware('can:applications.delete');
        Route::middleware('can:users.manage')->group(function () {
            Route::get('users', 'Api\UserRoleController@index');
            Route::patch('users/{user}/role', 'Api\UserRoleController@update');
            Route::patch('users/{user}/status', 'Api\UserRoleController@updateStatus');
        });
    });
});
