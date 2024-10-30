<?php

use Illuminate\Support\Facades\Route;
use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\AuthController;

Route::get('/shibboleth-login', [AuthController::class, 'shibbolethLogin'])->name('cu-auth.shibboleth-login');
