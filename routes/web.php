<?php

use App\Http\Controllers\Authentication\AdminAuthSSOController;
use App\Http\Controllers\Authentication\UserBasicAuthController;
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
        Route::get('admin/auth/sso/redirect', [AdminAuthSSOController::class, 'redirect'])->name('admin.auth.sso.redirect');
        Route::get('sso/callback', [AdminAuthSSOController::class, 'callback'])->name('admin.auth.sso.callback');

        // BASIC AUTHENTICATION
        Route::post('user/auth/login', [UserBasicAuthController::class, 'login'])->name('user.auth.login');
        Route::post('user/auth/login/otp/refresh', [UserBasicAuthController::class, 'refreshOtp'])->name('user.auth.login.otp.refresh');
        Route::post('user/auth/login/otp/verify', [UserBasicAuthController::class, 'verifyOtp'])->name('user.auth.login.otp.verify');
    });

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('admin/auth/token/validate', [AdminAuthSSOController::class, 'validateToken'])->name('admin.auth.token.validate');
        Route::post('user/auth/token/validate', [UserBasicAuthController::class, 'validateToken'])->name('user.auth.token.validate');

        Route::delete('admin/auth/logout', [AdminAuthSSOController::class, 'logout'])->name('admin.auth.logout');
        Route::delete('user/auth/logout', [UserBasicAuthController::class, 'logout'])->name('user.auth.logout');
    });
