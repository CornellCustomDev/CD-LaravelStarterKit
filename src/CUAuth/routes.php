<?php

use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\RemoteAuthenticationController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get('/shibboleth-login', [RemoteAuthenticationController::class, 'login'])->name('cu-auth.shibboleth-login');
    Route::get('/shibboleth-logout', [RemoteAuthenticationController::class, 'logout'])->name('cu-auth.shibboleth-logout');

    Route::get('/sso/login', [RemoteAuthenticationController::class, 'login'])->name('cu-auth.sso-login');
    Route::get('/sso/logout', [RemoteAuthenticationController::class, 'logout'])->name('cu-auth.sso-logout');
    Route::get('/sso/metadata', [RemoteAuthenticationController::class, 'metadata'])->name('cu-auth.sso-metadata');
    Route::post('/sso/acs', [RemoteAuthenticationController::class, 'acs'])->name('cu-auth.sso-acs')->withoutMiddleware([VerifyCsrfToken::class]);
});
