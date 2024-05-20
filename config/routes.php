<?php

use App\Controllers\AuthenticationsController;
use App\Controllers\ProblemsController;
use Core\Router\Route;

// Authentication
Route::get('/login', [AuthenticationsController::class, 'new'])->name('users.login');
Route::post('/login', [AuthenticationsController::class, 'authenticate'])->name('users.authenticate');

Route::middleware('auth')->group(function () {
    Route::get('/', [ProblemsController::class, 'index'])->name('root');

    // Create
    Route::get('/problems/new', [ProblemsController::class, 'new'])->name('problems.new');
    Route::post('/problems', [ProblemsController::class, 'create'])->name('problems.create');

    // Retrieve
    Route::get('/problems', [ProblemsController::class, 'index'])->name('problems.index');
    Route::get('/problems/page/{page}', [ProblemsController::class, 'index'])->name('problems.paginate');
    Route::get('/problems/{id}', [ProblemsController::class, 'show'])->name('problems.show');

    // Update
    Route::get('/problems/{id}/edit', [ProblemsController::class, 'edit'])->name('problems.edit');
    Route::put('/problems/{id}', [ProblemsController::class, 'update'])->name('problems.update');

    // Delete
    Route::delete('/problems/{id}', [ProblemsController::class, 'destroy'])->name('problems.destroy');

    // Logout
    Route::get('/logout', [AuthenticationsController::class, 'destroy'])->name('users.logout');
});
