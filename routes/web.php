<?php

use Illuminate\Support\Facades\Route;
use LucasKaiut\Okrestri\Controllers\ModuleController;

Route::middleware(['web'])
    ->prefix('orkestri')
    ->group(function () {
        Route::resource('modules', ModuleController::class);
        Route::post('modules/{module}/run-migration', [ModuleController::class, 'runMigration'])->name('modules.run-migration');
    });