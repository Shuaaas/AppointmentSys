<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Appointment Data Entry System
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('appointments')->name('appointments.')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::delete('/bulk', [AppointmentController::class, 'bulkDestroy'])->name('bulkDestroy');
    Route::get('/{appointment}/export-afa', [AppointmentController::class, 'exportAfa'])->name('exportAfa');
    Route::get('/{appointment}/checklist/download', [AppointmentController::class, 'downloadChecklist'])->name('downloadChecklist');
    Route::get('/{appointment}/rai/download', [AppointmentController::class, 'downloadRai'])->name('downloadRai');
    Route::get('/{appointment}/final-deliberation/download', [AppointmentController::class, 'downloadFinalDeliberation'])->name('downloadFinalDeliberation');
    Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
    Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
    Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
    Route::post('/{appointment}/conclude', [AppointmentController::class, 'conclude'])->name('conclude');

    Route::match(['get', 'post'], '/export/csv', [AppointmentController::class, 'exportCsv'])->name('export');

    Route::get('/trash/list', [AppointmentController::class, 'trash'])->name('trash');
    Route::post('/trash/{id}/restore', [AppointmentController::class, 'restore'])->name('restore');
    Route::delete('/trash/{id}/force', [AppointmentController::class, 'forceDelete'])->name('forceDelete');
});

Route::get('/history', HistoryController::class . '@index')->name('history.index');