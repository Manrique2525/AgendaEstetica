<?php

use App\Http\Controllers\Api\V1\AdminAgendaAppointmentController;
use App\Http\Controllers\Api\V1\AdminAgendaContextController;
use App\Http\Controllers\Api\V1\AdminAgendaLookupController;
use App\Http\Controllers\Api\V1\AdminAgendaMutationController;
use App\Http\Controllers\Api\V1\AdminAuthController;
use App\Http\Controllers\Api\V1\AdminBusinessProfileController;
use App\Http\Controllers\Api\V1\AdminProductCategoryController;
use App\Http\Controllers\Api\V1\AdminProductController;
use App\Http\Controllers\Api\V1\AdminProfessionalController;
use App\Http\Controllers\Api\V1\AdminProfessionalScheduleController;
use App\Http\Controllers\Api\V1\AdminServiceCategoryController;
use App\Http\Controllers\Api\V1\AdminServiceController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\PublicBookingAppointmentController;
use App\Http\Controllers\Api\V1\PublicBookingController;
use App\Http\Controllers\Api\V1\PublicProductController;
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
        ->middleware('throttle:public-booking-appointments')
        ->name('public.booking.appointments.store');
});

Route::prefix('admin/auth')->group(function (): void {
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('admin.auth.login');

    Route::post('/forgot-password', [AdminAuthController::class, 'forgotPassword'])
        ->middleware('throttle:admin-login')
        ->name('admin.auth.password.forgot');

    Route::middleware(['auth:sanctum', 'admin'])->group(function (): void {
        Route::get('/me', [AdminAuthController::class, 'me'])->name('admin.auth.me');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.auth.logout');
    });
});

Route::prefix('admin/agenda')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
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

Route::prefix('public')->group(function (): void {
    Route::get('/products', [PublicProductController::class, 'index'])
        ->middleware('throttle:public-booking-catalog')
        ->name('public.products.index');
});

Route::prefix('admin/business')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/profile', [AdminBusinessProfileController::class, 'show'])->name('admin.business.profile.show');
    Route::put('/profile', [AdminBusinessProfileController::class, 'update'])->name('admin.business.profile.update');
});

Route::prefix('admin/professionals')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/', [AdminProfessionalController::class, 'index'])->name('admin.professionals.index');
    Route::post('/', [AdminProfessionalController::class, 'store'])->name('admin.professionals.store');
    Route::get('/{professional}', [AdminProfessionalController::class, 'show'])->name('admin.professionals.show');
    Route::put('/{professional}', [AdminProfessionalController::class, 'update'])->name('admin.professionals.update');
    Route::patch('/{professional}/status', [AdminProfessionalController::class, 'status'])->name('admin.professionals.status');
    Route::delete('/{professional}', [AdminProfessionalController::class, 'destroy'])->name('admin.professionals.destroy');
    Route::get('/{professional}/schedules', [AdminProfessionalScheduleController::class, 'index'])->name('admin.professionals.schedules.index');
    Route::put('/{professional}/schedules', [AdminProfessionalScheduleController::class, 'update'])->name('admin.professionals.schedules.update');
});

Route::prefix('admin/catalog')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    Route::get('/service-categories', [AdminServiceCategoryController::class, 'index'])->name('admin.catalog.service-categories.index');
    Route::post('/service-categories', [AdminServiceCategoryController::class, 'store'])->name('admin.catalog.service-categories.store');
    Route::get('/service-categories/{serviceCategory}', [AdminServiceCategoryController::class, 'show'])->name('admin.catalog.service-categories.show');
    Route::put('/service-categories/{serviceCategory}', [AdminServiceCategoryController::class, 'update'])->name('admin.catalog.service-categories.update');
    Route::patch('/service-categories/{serviceCategory}/status', [AdminServiceCategoryController::class, 'status'])->name('admin.catalog.service-categories.status');
    Route::delete('/service-categories/{serviceCategory}', [AdminServiceCategoryController::class, 'destroy'])->name('admin.catalog.service-categories.destroy');

    Route::get('/services', [AdminServiceController::class, 'index'])->name('admin.catalog.services.index');
    Route::post('/services', [AdminServiceController::class, 'store'])->name('admin.catalog.services.store');
    Route::get('/services/{service}', [AdminServiceController::class, 'show'])->name('admin.catalog.services.show');
    Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('admin.catalog.services.update');
    Route::patch('/services/{service}/status', [AdminServiceController::class, 'status'])->name('admin.catalog.services.status');
    Route::delete('/services/{service}', [AdminServiceController::class, 'destroy'])->name('admin.catalog.services.destroy');

    Route::get('/product-categories', [AdminProductCategoryController::class, 'index'])->name('admin.catalog.product-categories.index');
    Route::post('/product-categories', [AdminProductCategoryController::class, 'store'])->name('admin.catalog.product-categories.store');
    Route::put('/product-categories/{productCategory}', [AdminProductCategoryController::class, 'update'])->name('admin.catalog.product-categories.update');
    Route::patch('/product-categories/{productCategory}/status', [AdminProductCategoryController::class, 'status'])->name('admin.catalog.product-categories.status');
    Route::delete('/product-categories/{productCategory}', [AdminProductCategoryController::class, 'destroy'])->name('admin.catalog.product-categories.destroy');

    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.catalog.products.index');
    Route::post('/products', [AdminProductController::class, 'store'])->name('admin.catalog.products.store');
    Route::get('/products/{product}', [AdminProductController::class, 'show'])->name('admin.catalog.products.show');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('admin.catalog.products.update');
    Route::patch('/products/{product}/status', [AdminProductController::class, 'status'])->name('admin.catalog.products.status');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('admin.catalog.products.destroy');
});
