<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ArtifactController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\AuditController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Projects
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::patch('/projects/{project}/status', [ProjectController::class, 'transition']);
        Route::delete('/projects/{project}', [ProjectController::class, 'archive']);

        // Artifacts (nested under project)
        Route::get('/projects/{project}/artifacts', [ArtifactController::class, 'index']);
        Route::post('/projects/{project}/artifacts', [ArtifactController::class, 'store']);
        Route::get('/projects/{project}/artifacts/{artifact}', [ArtifactController::class, 'show']);
        Route::put('/projects/{project}/artifacts/{artifact}', [ArtifactController::class, 'update']);
        Route::patch('/projects/{project}/artifacts/{artifact}/status', [ArtifactController::class, 'updateStatus']);

        // Modules (nested under project)
        Route::get('/projects/{project}/modules', [ModuleController::class, 'index']);
        Route::post('/projects/{project}/modules', [ModuleController::class, 'store']);
        Route::get('/projects/{project}/modules/{module}', [ModuleController::class, 'show']);
        Route::put('/projects/{project}/modules/{module}', [ModuleController::class, 'update']);
        Route::post('/projects/{project}/modules/{module}/validate', [ModuleController::class, 'validateModule']);
        Route::delete('/projects/{project}/modules/{module}', [ModuleController::class, 'destroy']);

        // Audit
        Route::get('/projects/{project}/audit', [AuditController::class, 'timeline']);
    });
});
