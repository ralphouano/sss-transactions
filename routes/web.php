<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/intern/dashboard');

Route::get('/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::redirect('/settings', '/profile');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/transaction-types', [ProfileController::class, 'storeTransactionType'])
        ->middleware('role:admin')
        ->name('profile.transaction-types.store');
    Route::patch('/profile/transaction-types/{transactionType}', [ProfileController::class, 'updateTransactionType'])
        ->middleware('role:admin')
        ->name('profile.transaction-types.update');
    Route::patch('/profile/submission-pin', [ProfileController::class, 'updateSubmissionPin'])
        ->middleware('role:admin')
        ->name('profile.submission-pin.update');
});

Route::prefix('intern')->name('intern.')->group(function () {
    Route::get('/dashboard', [InternController::class, 'dashboard'])->name('dashboard');
    Route::post('/record', [InternController::class, 'store'])
        ->middleware('throttle:intern-record')
        ->name('record');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/export/{reportExport}/status', [ReportController::class, 'exportStatus'])->name('reports.export.status');
    Route::get('/reports/export/{reportExport}/download', [ReportController::class, 'exportDownload'])->name('reports.export.download');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
});

require __DIR__.'/auth.php';
