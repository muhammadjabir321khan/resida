<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Billing\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Rental\LeaseDocumentController;
use App\Http\Controllers\Rental\LeaseController;
use App\Http\Controllers\Rental\MaintenanceRequestController;
use App\Http\Controllers\Rental\MessageController;
use App\Http\Controllers\Rental\PropertyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Rental\RentalTenantController;
use App\Http\Controllers\Rental\RentalTransactionController;
use App\Http\Controllers\Rental\RentalUnitController;
use App\Http\Controllers\Rental\ReportController;
use App\Http\Controllers\Rental\RentPaymentInstallmentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Tenant\TenantInviteController;
use App\Http\Controllers\Tenant\TenantLeaseDocumentController;
use App\Http\Controllers\Tenant\TenantMaintenanceController;
use App\Http\Controllers\Tenant\TenantMessageController;
use App\Http\Controllers\Tenant\TenantRentPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('tenant/invite/{token}', [TenantInviteController::class, 'show'])->name('tenant.invite.show');

Route::middleware(['auth', 'verified'])->prefix('billing')->name('billing.')->group(function (): void {
    Route::get('/plans', [PlanController::class, 'index'])->name('plans');
    Route::post('/checkout', [PlanController::class, 'checkout'])->name('checkout');
    Route::get('/success', [PlanController::class, 'success'])->name('success');
});

Route::middleware(['auth', 'verified', 'subscribed'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function (): void {
    Route::post('rent/payments/{paymentId}/checkout', [TenantRentPaymentController::class, 'checkout'])
        ->whereNumber('paymentId')
        ->name('rent.checkout');
    Route::get('rent/success', [TenantRentPaymentController::class, 'success'])->name('rent.success');
    Route::get('maintenance', [TenantMaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('maintenance/create', [TenantMaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('maintenance', [TenantMaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('documents', [TenantLeaseDocumentController::class, 'index'])->name('documents.index');
    Route::get('documents/{documentId}/download', [TenantLeaseDocumentController::class, 'download'])
        ->whereNumber('documentId')
        ->name('documents.download');
    Route::get('messages', [TenantMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{thread}', [TenantMessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{thread}', [TenantMessageController::class, 'store'])->name('messages.store');
});

Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function (): void {
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth', 'verified', 'subscribed', 'landlord.rental'])->prefix('rental')->name('rental.')->group(function (): void {
    Route::get('properties/data', [PropertyController::class, 'data'])->name('properties.data');
    Route::resource('properties', PropertyController::class)->except(['show']);

    Route::get('units/data', [RentalUnitController::class, 'data'])->name('units.data');
    Route::resource('units', RentalUnitController::class)->except(['show']);

    Route::get('tenants/data', [RentalTenantController::class, 'data'])->name('tenants.data');
    Route::post('tenants/{tenant}/invite', [RentalTenantController::class, 'sendInvite'])->name('tenants.invite');
    Route::resource('tenants', RentalTenantController::class)->except(['show']);

    Route::get('leases/data', [LeaseController::class, 'data'])->name('leases.data');
    Route::resource('leases', LeaseController::class)->except(['show']);
    Route::post('leases/{lease}/documents', [LeaseDocumentController::class, 'store'])->name('leases.documents.store');
    Route::delete('leases/{lease}/documents/{document}', [LeaseDocumentController::class, 'destroy'])->name('leases.documents.destroy');
    Route::get('leases/{lease}/documents/{document}/download', [LeaseDocumentController::class, 'download'])->name('leases.documents.download');
    Route::post('leases/{lease}/message', [MessageController::class, 'startFromLease'])->name('leases.message.start');

    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{thread}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{thread}', [MessageController::class, 'store'])->name('messages.store');

    Route::get('maintenance-requests/data', [MaintenanceRequestController::class, 'data'])->name('maintenance-requests.data');
    Route::resource('maintenance-requests', MaintenanceRequestController::class)->except(['show']);

    Route::get('transactions/data', [RentalTransactionController::class, 'data'])->name('transactions.data');
    Route::resource('transactions', RentalTransactionController::class)->except(['show']);

    Route::get('payments/data', [RentPaymentInstallmentController::class, 'data'])->name('payments.data');
    Route::resource('payments', RentPaymentInstallmentController::class)->only(['index', 'edit', 'update', 'destroy']);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::resource('users', UserController::class)->except(['show']);

    Route::get('roles/data', [RoleController::class, 'data'])->name('roles.data');
    Route::resource('roles', RoleController::class)->except(['show']);

    Route::get('permissions/data', [PermissionController::class, 'data'])->name('permissions.data');
    Route::resource('permissions', PermissionController::class)->except(['show']);

    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
