<?php

use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\AuthController;
use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\PhpSamlController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get('/shibboleth-login', [AuthController::class, 'shibbolethLogin'])->name('cu-auth.shibboleth-login');
    Route::get('/shibboleth-logout', [AuthController::class, 'shibbolethLogout'])->name('cu-auth.shibboleth-logout');

    Route::get('/saml/login', [PhpSamlController::class, 'samlLogin'])->name('cu-auth.saml-login');
    Route::get('/saml/logout', [PhpSamlController::class, 'samlLogout'])->name('cu-auth.saml-logout');
    Route::post('/saml/acs', [PhpSamlController::class, 'samlAcs']);
    Route::get('/saml/metadata', [PhpSamlController::class, 'samlMetadata']);
});
