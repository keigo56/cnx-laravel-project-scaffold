<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->delete('/auth/logout', [\App\Http\Controllers\Authentication\AzureSSOController::class, 'sso_logout']);

Route::post('/datatable/users', [\App\Http\Controllers\Api\UserController::class, 'dataset']);
Route::post('/datatable/users/column-distinct-values', [\App\Http\Controllers\Api\UserController::class, 'column_distinct_values']);
Route::post('/datatable/users/export', [\App\Http\Controllers\Api\UserController::class, 'export']);

Route::post('/datatable/roles', [\App\Http\Controllers\Api\RoleController::class, 'dataset']);
Route::post('/datatable/roles/column-distinct-values', [\App\Http\Controllers\Api\RoleController::class, 'column_distinct_values']);
Route::post('/datatable/roles/export', [\App\Http\Controllers\Api\RoleController::class, 'export']);

Route::post('/datatable/permissions', [\App\Http\Controllers\Api\PermissionController::class, 'dataset']);
Route::post('/datatable/permissions/column-distinct-values', [\App\Http\Controllers\Api\PermissionController::class, 'column_distinct_values']);
Route::post('/datatable/permissions/export', [\App\Http\Controllers\Api\PermissionController::class, 'export']);
