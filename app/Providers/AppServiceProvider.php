<?php

namespace App\Providers;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\RentalTenant;
use App\Models\RentalTransaction;
use App\Models\RentalUnit;
use App\Models\RentPaymentInstallment;
use App\Observers\LeaseObserver;
use App\Observers\RentPaymentInstallmentObserver;
use App\View\Composers\HeaderNotificationsComposer;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.partials.header', HeaderNotificationsComposer::class);

        Lease::observe(LeaseObserver::class);
        RentPaymentInstallment::observe(RentPaymentInstallmentObserver::class);

        Route::model('tenant', RentalTenant::class);
        Route::model('transaction', RentalTransaction::class);
        Route::model('maintenance_request', MaintenanceRequest::class);
        Route::model('unit', RentalUnit::class);
        Route::model('payment', RentPaymentInstallment::class);
    }
}
