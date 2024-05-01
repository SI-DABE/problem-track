<?php

use App\Controllers\ProblemsController;
use Core\Router\Route;

Route::get('/', [ProblemsController::class, 'index'])->name('root');

Route::get('/problems', [ProblemsController::class, 'index'])->name('problems.index');
Route::get('/problems/new', [ProblemsController::class, 'new'])->name('problems.new');
