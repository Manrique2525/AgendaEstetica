<?php

use App\Http\Controllers\Api\V1\AdminAgendaAppointmentController;
use App\Http\Controllers\Api\V1\AdminAgendaContextController;
use App\Http\Controllers\Api\V1\AdminAgendaLookupController;
use App\Http\Controllers\Api\V1\AdminAgendaMutationController;
use App\Http\Controllers\Api\V1\AdminAuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\PublicBookingAppointmentController;
use App\Http\Controllers\Api\V1\PublicBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('api.health');

Route::prefix('public/booking')->group(function (): void {
    Route::get('/context', [PublicBookingController::class, 'context'])
        ->middleware('throttle:public-booking-catalog')
        ->name('public.booking.context');
    Route::get('/services', [PublicBookingController::class, 'services'])
        ->middleware('throttle:public-booking-catalog')
        ->name('public.booking.services');
    Route::get('/professionals', [PublicBookingController::class, 'professionals'])
        ->middleware('throttle:public-booking-professionals')
        ->name('public.booking.professionals');
    Route::get('/availability', [PublicBookingController::class, 'availability'])
        ->middleware('throttle:public-booking-availability')
        ->name('public.booking.availability');
    Route::post('/appointments', [PublicBookingAppointmentController::class, 'store'])
        ->name('public.booking.appointments.store');
});

Route::prefix('admin/auth')->group(function (): void {
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('admin.auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AdminAuthController::class, 'me'])->name('admin.auth.me');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.auth.logout');
    });
});

Route::prefix('admin/agenda')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/context', AdminAgendaContextController::class)
        ->name('admin.agenda.context');
    Route::get('/appointments', [AdminAgendaAppointmentController::class, 'index'])
        ->name('admin.agenda.appointments.index');
    Route::get('/appointments/{appointment}', [AdminAgendaAppointmentController::class, 'show'])
        ->name('admin.agenda.appointments.show');
    Route::get('/customers', [AdminAgendaLookupController::class, 'customers'])
        ->name('admin.agenda.customers.index');
    Route::get('/services', [AdminAgendaLookupController::class, 'services'])
        ->name('admin.agenda.services.index');
    Route::get('/professionals', [AdminAgendaLookupController::class, 'professionals'])
        ->name('admin.agenda.professionals.index');
    Route::post('/appointments', [AdminAgendaMutationController::class, 'store'])
        ->name('admin.agenda.appointments.store');
    Route::post('/appointments/{appointment}/reschedule', [AdminAgendaMutationController::class, 'reschedule'])
        ->name('admin.agenda.appointments.reschedule');
    Route::post('/appointments/{appointment}/cancel', [AdminAgendaMutationController::class, 'cancel'])
        ->name('admin.agenda.appointments.cancel');
    Route::post('/appointments/{appointment}/complete', [AdminAgendaMutationController::class, 'complete'])
        ->name('admin.agenda.appointments.complete');
    Route::post('/appointments/{appointment}/no-show', [AdminAgendaMutationController::class, 'noShow'])
        ->name('admin.agenda.appointments.no-show');
});
