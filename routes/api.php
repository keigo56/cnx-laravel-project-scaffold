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

Route::middleware('auth:sanctum')->delete('/auth/logout', [\App\Http\Controllers\Authentication\AzureSSOController::class, 'sso_logout']);
Route::middleware('auth:sanctum')->group(function(){

    Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'user']);

    Route::post('/datatable/users', [\App\Http\Controllers\Api\UserController::class, 'dataset']);
    Route::post('/datatable/users/column-distinct-values', [\App\Http\Controllers\Api\UserController::class, 'column_distinct_values']);
    Route::post('/datatable/users/export', [\App\Http\Controllers\Api\UserController::class, 'export']);
    Route::get('/users/employee/search', [\App\Http\Controllers\Api\UserController::class, 'search_employee']);
    Route::get('/users/roles', [\App\Http\Controllers\Api\UserController::class, 'roles']);
    Route::post('/users/store', [\App\Http\Controllers\Api\UserController::class, 'store']);
    Route::put('/users/update', [\App\Http\Controllers\Api\UserController::class, 'update']);
    Route::delete('/users/delete', [\App\Http\Controllers\Api\UserController::class, 'delete']);


    Route::post('/datatable/roles', [\App\Http\Controllers\Api\RoleController::class, 'dataset']);
    Route::post('/datatable/roles/column-distinct-values', [\App\Http\Controllers\Api\RoleController::class, 'column_distinct_values']);
    Route::post('/datatable/roles/export', [\App\Http\Controllers\Api\RoleController::class, 'export']);
    Route::get('/datatable/roles/get-permissions', [\App\Http\Controllers\Api\RoleController::class, 'getPermissions']);
    Route::get('/datatable/roles/{role}/permissions', [\App\Http\Controllers\Api\RoleController::class, 'getRolePermissions']);
    Route::post('/datatable/roles/add', [\App\Http\Controllers\Api\RoleController::class, 'add']);
    Route::put('/datatable/roles/update', [\App\Http\Controllers\Api\RoleController::class, 'update']);
    Route::delete('/datatable/roles/delete', [\App\Http\Controllers\Api\RoleController::class, 'delete']);

    Route::post('/datatable/permissions', [\App\Http\Controllers\Api\PermissionController::class, 'dataset']);
    Route::post('/datatable/permissions/column-distinct-values', [\App\Http\Controllers\Api\PermissionController::class, 'column_distinct_values']);
    Route::post('/datatable/permissions/export', [\App\Http\Controllers\Api\PermissionController::class, 'export']);

    Route::post('/datatable/audit-trail/logs', [\App\Http\Controllers\Api\AuditTrailController::class, 'dataset']);
    Route::post('/datatable/audit-trail/logs/column-distinct-values', [\App\Http\Controllers\Api\AuditTrailController::class, 'column_distinct_values']);
    Route::post('/datatable/audit-trail/logs/export', [\App\Http\Controllers\Api\AuditTrailController::class, 'export']);
});
