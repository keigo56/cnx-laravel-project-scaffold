<?php

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
        Route::get('auth/sso/redirect', [AuthSSOController::class, 'redirect'])->name('auth.sso.redirect');
        Route::get('sso/callback', [AuthSSOController::class, 'callback'])->name('auth.sso.callback');

        // BASIC AUTHENTICATION WITH OTP
        // Route::post('user/auth/login', [UserBasicAuthController::class, 'login'])->name('user.auth.login');
        // Route::post('user/auth/login/otp/refresh', [UserBasicAuthController::class, 'refreshOtp'])->name('user.auth.login.otp.refresh');
        // Route::post('user/auth/login/otp/verify', [UserBasicAuthController::class, 'verifyOtp'])->name('user.auth.login.otp.verify');
    });

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('auth/token/validate', [AuthSSOController::class, 'validateToken'])->name('auth.token.validate');

        Route::delete('auth/logout', [AuthSSOController::class, 'logout'])->name('auth.logout');
    });
