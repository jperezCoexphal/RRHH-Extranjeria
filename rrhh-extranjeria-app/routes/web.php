<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Redirect root to login or dashboard
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Volt::route('/login', 'auth.login')->name('login');
    Volt::route('/register', 'auth.register')->name('register');
});

// Logout Route
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Serve template files from resources/pdf
Route::get('/templates/file/{filename}', function (string $filename) {
    $path = resource_path('pdf/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    return response()->file($path, [
        'Content-Type' => $mimeTypes[$extension] ?? 'application/octet-stream',
    ]);
})->name('templates.file');

// Protected Routes (Auth required)
Route::prefix('/v1')->middleware('auth')->group(function () {

    // Dashboard
    Volt::route('/', 'dashboard')->name('dashboard');

    // Employers - Volt Components
    Route::prefix('employers')->name('employers.')->group(function () {
        Volt::route('/', 'employers.index')->name('index');
        Volt::route('/create', 'employers.create')->name('create');
        Volt::route('/{employer}', 'employers.show')->name('show');
        Volt::route('/{employer}/edit', 'employers.edit')->name('edit');
    });

    // Foreigners - Volt Components
    Route::prefix('foreigners')->name('foreigners.')->group(function () {
        Volt::route('/', 'foreigners.index')->name('index');
        Volt::route('/create', 'foreigners.create')->name('create');
        Volt::route('/{foreigner}', 'foreigners.show')->name('show');
        Volt::route('/{foreigner}/edit', 'foreigners.edit')->name('edit');
    });

    // Inmigration Files (Expedientes) - Volt Components
    Route::prefix('inmigration-files')->name('inmigration-files.')->group(function () {
        Volt::route('/', 'inmigration-files.index')->name('index');
        Volt::route('/create', 'inmigration-files.create')->name('create');
        Volt::route('/{inmigrationFile}', 'inmigration-files.show')->name('show');
        Volt::route('/{inmigrationFile}/edit', 'inmigration-files.edit')->name('edit');
    });

    // Documents - Generación en caliente (no se almacenan)
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/{inmigrationFileId}/check', [DocumentController::class, 'checkAvailability'])->name('check');
        Route::get('/{inmigrationFileId}/generate', [DocumentController::class, 'generatePack'])->name('generate');
        Route::get('/{inmigrationFileId}/generate-ex', [DocumentController::class, 'generateModeloEX'])->name('generate-ex');
    });

    // Templates - Volt Components
    Route::prefix('templates')->name('templates.')->group(function () {
        Volt::route('/', 'templates.index')->name('index');
        Volt::route('/create', 'templates.create')->name('create');
        Volt::route('/{id}', 'templates.show')->name('show');
        Volt::route('/{id}/edit', 'templates.edit')->name('edit');
    });

    // Checklist (Requisitos del expediente) - Volt + API
    Route::prefix('checklist')->name('checklist.')->group(function () {
        // Vista principal (Livewire Volt)
        Volt::route('/{inmigrationFileId}', 'checklist.show')->name('index');
        // API endpoints
        Route::get('/{inmigrationFileId}/summary', [ChecklistController::class, 'summary'])->name('summary');
        Route::get('/{inmigrationFileId}/upcoming', [ChecklistController::class, 'upcoming'])->name('upcoming');
    });

    // Requirement Templates (Plantillas de requisitos) - Volt Components
    Route::prefix('requirement-templates')->name('requirement-templates.')->group(function () {
        Volt::route('/', 'requirement-templates.index')->name('index');
        Volt::route('/create', 'requirement-templates.create')->name('create');
        Volt::route('/{requirementTemplate}/edit', 'requirement-templates.edit')->name('edit');
    });
});
