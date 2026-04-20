<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\InternManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/intern/dashboard');

Route::get('/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('intern')->name('intern.')->group(function () {
    Route::get('/dashboard', [InternController::class, 'dashboard'])->name('dashboard');
    Route::post('/record', [InternController::class, 'store'])
        ->middleware('throttle:intern-record')
        ->name('record');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/interns', [InternManagementController::class, 'index'])->name('interns.index');
    Route::post('/interns', [InternManagementController::class, 'store'])->name('interns.store');
    Route::patch('/interns/{user}', [InternManagementController::class, 'update'])->name('interns.update');
    Route::delete('/interns/{user}', [InternManagementController::class, 'destroy'])->name('interns.destroy');
    
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});

require __DIR__.'/auth.php';
