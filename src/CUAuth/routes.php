<?php

use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/shibboleth-login', [AuthController::class, 'shibbolethLogin'])->name('cu-auth.shibboleth-login');
