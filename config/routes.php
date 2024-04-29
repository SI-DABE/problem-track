<?php

use App\Controllers\ProblemsController;
use Core\Router\Route;

Route::get('/',             [ProblemsController::class, 'index']);

Route::get('/problems',     [ProblemsController::class, 'index']);
Route::get('/problems/new', [ProblemsController::class, 'new']);
