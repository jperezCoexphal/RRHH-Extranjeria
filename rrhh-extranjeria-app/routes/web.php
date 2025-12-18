<?php

use App\Http\Controllers\EmployerController;
use App\Http\Controllers\ForeignerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('employers.index');
});

Route::prefix('/v1')->group(function (){
    // Employers
    Route::resource('employers', EmployerController::class);

    // Foreigners
    Route::resource('foreigners', ForeignerController::class);
});
