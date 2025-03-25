<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    
    // Soft delete
    Route::get('/tasks/trashed', [TaskController::class, 'trashed']); 
    Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore']);
});
