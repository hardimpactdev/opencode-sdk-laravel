<?php

use HardImpact\OpenCode\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::prefix('tasks')->group(function () {
        Route::post('refine-and-create', [TasksController::class, 'refineAndCreate'])
            ->name('tasks.refine-and-create');

        Route::post('create-direct', [TasksController::class, 'createDirect'])
            ->name('tasks.create-direct');
    });
});