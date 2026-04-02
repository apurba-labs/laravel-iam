<?php

use Illuminate\Support\Facades\Route;

use ApurbaLabs\IAM\Http\Controllers\Api\CheckController;
use ApurbaLabs\IAM\Http\Controllers\Api\RoleController;
use ApurbaLabs\IAM\Http\Controllers\Api\PermissionController;
use ApurbaLabs\IAM\Http\Controllers\Api\UserRoleController;

Route::prefix('api/iam')->middleware(['api', 'auth:sanctum'])->group(function () {
    // CHECK PERMISSION
    Route::post('/check', [CheckController::class, 'check']);

    // ROLES
    Route::apiResource('/roles', RoleController::class);

    // PERMISSIONS
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('permissions', [PermissionController::class, 'store']);

    // ROLE -> PERMISSION (Management within RoleController)
    Route::get('roles/{role}/permissions', [RoleController::class, 'permissions']);
    Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

    // USER -> ROLE
    Route::post('users/{user}/roles', [UserRoleController::class, 'attach']);
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'detach']);
});
