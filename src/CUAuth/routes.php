<?php

use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\AuthController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get('/sso/login', [AuthController::class, 'login'])->name('cu-auth.sso-login');
    Route::get('/sso/logout', [AuthController::class, 'logout'])->name('cu-auth.sso-logout');
    Route::get('/sso/metadata', [AuthController::class, 'metadata'])->name('cu-auth.sso-metadata');
    Route::post('/sso/acs', [AuthController::class, 'acs'])->name('cu-auth.sso-acs')->withoutMiddleware([VerifyCsrfToken::class]);

    // Legacy
    Route::get('/shibboleth-login', [AuthController::class, 'login'])->name('cu-auth.shibboleth-login');
    Route::get('/shibboleth-logout', [AuthController::class, 'logout'])->name('cu-auth.shibboleth-logout');
});
