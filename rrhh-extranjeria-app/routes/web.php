<?php

use App\Http\Controllers\EmployerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/v1')->group(function (){
    // Employers
    Route::resource('employers', EmployerController::class);
});
