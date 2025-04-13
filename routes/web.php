<?php

use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Authentication\AuthSSOController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')
    ->group(function () {

        // SSO AUTHENTICATION
        Route::get('{role}/auth/sso/redirect', [AuthSSOController::class, 'sso_redirect'])->name('auth.sso.redirect');
        Route::get('sso/callback', [AuthSSOController::class, 'sso_callback'])->name('auth.sso.callback');

        // NON SSO AUTHENTICATION
        Route::post('{role}/auth/login', [AuthController::class, 'login']);
        Route::post('{role}/auth/login/otp/refresh', [AuthController::class, 'refresh_otp']);
        Route::post('{role}/auth/login/otp/verify', [AuthController::class, 'verify_otp']);
    });

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('{role}/auth/token/validate', [AuthController::class, 'validate_token'])->name('auth.token.validate');
        Route::delete('{role}/auth/logout', [AuthController::class, 'auth_logout'])->name('auth.logout');
    });
