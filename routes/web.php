<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PlantillaItemController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — PAMS (Personnel Appointment Management System)
|--------------------------------------------------------------------------
| Public routes first. Everything else lives inside the auth+role
| middleware groups below — there are NO duplicate/unprotected copies
| of any protected route in this file.
*/

Route::redirect('/', '/login');

// --- PUBLIC (guests only — redirects away if already logged in) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// --- AUTHENTICATED ---
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Dashboard access for HR, manager, and admin.
    // Admin sees the new admin dashboard; HR still sees the traditional overview.
    Route::middleware([EnsureUserHasRole::class.':hr,manager,admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    });

    Route::prefix('appointments')->name('appointments.')->group(function () {

        // View access: HR, Records, Manager — all current non-admin roles.
        // Manager gets read-only because AppointmentPolicy::before() denies
        // every write ability for that role, not because it's excluded here.
        Route::middleware([EnsureUserHasRole::class.':hr,records,manager'])->group(function () {
            Route::get('/', [AppointmentController::class, 'index'])->name('index');
            Route::get('/archive', [AppointmentController::class, 'archive'])->name('archive');
            Route::get('/{appointment}', [AppointmentController::class, 'show'])->whereNumber('appointment')->name('show');
        });

        // HR only: create, full update, archive/conclude, document generation.
        Route::middleware([EnsureUserHasRole::class.':hr'])->group(function () {
            Route::get('/create', [AppointmentController::class, 'create'])->name('create');
            Route::post('/', [AppointmentController::class, 'store'])->name('store');
            Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
            Route::post('/{appointment}/conclude', [AppointmentController::class, 'conclude'])->name('conclude');

            Route::get('/plantilla-search', [PlantillaItemController::class, 'search'])->name('plantilla.search');
            Route::get('/salary', [PlantillaItemController::class, 'salary'])->name('salary');

            Route::get('/{appointment}/export-afa', [AppointmentController::class, 'exportAfa'])->name('exportAfa');
            Route::get('/{appointment}/checklist/download', [AppointmentController::class, 'downloadChecklist'])->name('downloadChecklist');
            Route::get('/{appointment}/rai/download', [AppointmentController::class, 'downloadRai'])->name('downloadRai');
            Route::get('/{appointment}/final-deliberation/download', [AppointmentController::class, 'downloadFinalDeliberation'])->name('downloadFinalDeliberation');
            Route::match(['get', 'post'], '/export/csv', [AppointmentController::class, 'exportCsv'])->name('export');
            Route::post('/export/monitoring', [AppointmentController::class, 'exportMonitoringCsv'])->name('archive.exportMonitoring');
        });

        // Records only: the single narrow field edit.
        Route::middleware([EnsureUserHasRole::class.':records'])->group(function () {
            Route::patch('/{appointment}/transaction-number', [AppointmentController::class, 'updateTransactionNumber'])
                ->name('updateTransactionNumber');
        });

        Route::middleware([EnsureUserHasRole::class.':admin'])->group(function () {
            Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])
                ->name('destroy');
            Route::get('/trash', [AppointmentController::class, 'trash'])
                ->name('trash');
            Route::post('/{appointment}/restore', [AppointmentController::class, 'restore'])
                ->name('restore');
            Route::delete('/{appointment}/force-delete', [AppointmentController::class, 'forceDelete'])
                ->name('forceDelete');
            Route::delete('/bulk-destroy', [AppointmentController::class, 'bulkDestroy'])
                ->name('bulkDestroy');
        });

        // Admin no longer has appointment management access here.
        // Admin access is limited to the dedicated user management section.
    });

    // History: viewable by all 4 roles (matches your earlier RBAC matrix).
    Route::middleware([EnsureUserHasRole::class.':hr,records,manager,admin'])->group(function () {
        Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    });

    // Admin only: user & role management, including approving registration requests.
    Route::middleware([EnsureUserHasRole::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users/create', [UserController::class, 'addUser'])->name('users.add');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/role', [UserController::class, 'assignRole'])->name('users.assignRole');
        Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

        Route::get('/passwords', [UserController::class, 'passwords'])->name('passwords.index');
        Route::post('/passwords/{user}', [UserController::class, 'resetPassword'])->name('passwords.reset');
    });

    // Public invitation confirmation (no auth required).
    Route::get('/invitation/accept/{token}', [\App\Http\Controllers\InvitationController::class, 'show'])->name('invitation.accept');
    Route::post('/invitation/accept/{token}', [\App\Http\Controllers\InvitationController::class, 'accept'])->name('invitation.confirm');
});
