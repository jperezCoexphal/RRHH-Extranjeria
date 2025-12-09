<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/users', [UserController::class, 'index'])->name('user.index');
Route::get('/users/create',[UserController::class, 'create'])->name('user.create');