<?php

use Illuminate\Support\Facades\Route;
use Nikolaynesov\LaravelCommandAssistant\CommandController;

Route::prefix('dev-tools')->middleware('verify.laravel-command-assistant.key')->group(function () {
    Route::get('/available-commands', [CommandController::class, 'available'])->name('command-assistant.available');
    Route::post('/execute-command', [CommandController::class, 'execute'])->name('command-assistant.execute');
});