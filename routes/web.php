<?php

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

Route::get('sso/login', [\App\Http\Controllers\Authentication\AzureSSOController::class, 'app_sso_login'])->name('app.sso.login');
Route::get('sso/callback', [\App\Http\Controllers\Authentication\AzureSSOController::class, 'app_sso_callback'])->name('app.sso.callback');

Route::post('auth/token/validate', [\App\Http\Controllers\Authentication\AuthController::class, 'validate_token'])->middleware('auth:sanctum');

Route::get('/', function () {
    return view('welcome');
});
