<?php

use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get('/shibboleth-login', [AuthController::class, 'shibbolethLogin'])->name('cu-auth.shibboleth-login');
    Route::get('/shibboleth-logout', [AuthController::class, 'shibbolethLogout'])->name('cu-auth.shibboleth-logout');
});
