<?php

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

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'user']);

    Route::post('/datatable/users', [\App\Http\Controllers\Api\UserController::class, 'dataset'])->middleware(['can:view_users']);
    Route::post('/datatable/users/column-distinct-values', [\App\Http\Controllers\Api\UserController::class, 'column_distinct_values'])->middleware(['can:view_users']);
    Route::post('/datatable/users/export', [\App\Http\Controllers\Api\UserController::class, 'export'])->middleware(['can:export_users']);
    Route::get('/users/employee/search', [\App\Http\Controllers\Api\UserController::class, 'search_employee'])->middleware(['can:view_users']);
    Route::get('/users/roles', [\App\Http\Controllers\Api\UserController::class, 'roles'])->middleware(['can:add_users']);
    Route::post('/users/store', [\App\Http\Controllers\Api\UserController::class, 'store'])->middleware(['can:add_users']);
    Route::put('/users/update', [\App\Http\Controllers\Api\UserController::class, 'update'])->middleware(['can:edit_users']);
    Route::delete('/users/delete', [\App\Http\Controllers\Api\UserController::class, 'delete'])->middleware(['can:delete_users']);

    Route::post('/datatable/roles', [\App\Http\Controllers\Api\RoleController::class, 'dataset'])->middleware(['can:view_roles']);
    Route::post('/datatable/roles/column-distinct-values', [\App\Http\Controllers\Api\RoleController::class, 'column_distinct_values'])->middleware(['can:view_roles']);
    Route::post('/datatable/roles/export', [\App\Http\Controllers\Api\RoleController::class, 'export'])->middleware(['can:export_roles']);
    Route::get('/datatable/roles/get-permissions', [\App\Http\Controllers\Api\RoleController::class, 'getPermissions'])->middleware(['can:add_roles']);
    Route::get('/datatable/roles/{role}/permissions', [\App\Http\Controllers\Api\RoleController::class, 'getRolePermissions'])->middleware(['can:add_roles']);
    Route::post('/datatable/roles/add', [\App\Http\Controllers\Api\RoleController::class, 'add'])->middleware(['can:add_roles']);
    Route::put('/datatable/roles/update', [\App\Http\Controllers\Api\RoleController::class, 'update'])->middleware(['can:edit_roles']);
    Route::delete('/datatable/roles/delete', [\App\Http\Controllers\Api\RoleController::class, 'delete'])->middleware(['can:delete_roles']);

    Route::post('/datatable/permissions', [\App\Http\Controllers\Api\PermissionController::class, 'dataset'])->middleware(['can:view_permissions']);
    Route::post('/datatable/permissions/column-distinct-values', [\App\Http\Controllers\Api\PermissionController::class, 'column_distinct_values'])->middleware(['can:view_permissions']);
    Route::post('/datatable/permissions/export', [\App\Http\Controllers\Api\PermissionController::class, 'export'])->middleware(['can:export_permissions']);

    Route::post('/datatable/audit-trail/logs', [\App\Http\Controllers\Api\AuditTrailController::class, 'dataset'])->middleware(['can:view_logs']);
    Route::post('/datatable/audit-trail/logs/column-distinct-values', [\App\Http\Controllers\Api\AuditTrailController::class, 'column_distinct_values'])->middleware(['can:view_logs']);
    Route::post('/datatable/audit-trail/logs/export', [\App\Http\Controllers\Api\AuditTrailController::class, 'export'])->middleware(['can:export_logs']);
});
