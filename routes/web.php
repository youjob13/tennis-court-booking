<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CourtController as AdminCourtController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CourtController::class, 'index'])->name('courts.index');
Route::get('/courts/{court}', [CourtController::class, 'show'])->name('courts.show')->middleware('auth');

// Booking routes
Route::middleware('auth')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/payment', [BookingController::class, 'showPayment'])->name('bookings.payment');
    Route::post('/bookings/{booking}/payment', [BookingController::class, 'processPayment'])->name('bookings.payment.process');
    Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'showConfirmation'])->name('bookings.confirmation');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Court management
    Route::get('/courts', [AdminCourtController::class, 'index'])->name('courts.index');
    Route::get('/courts/create', [AdminCourtController::class, 'create'])->name('courts.create');
    Route::post('/courts', [AdminCourtController::class, 'store'])->name('courts.store');
    Route::patch('/courts/{court}/disable', [AdminCourtController::class, 'disable'])->name('courts.disable');
    Route::patch('/courts/{court}/enable', [AdminCourtController::class, 'enable'])->name('courts.enable');
    Route::delete('/courts/{court}', [AdminCourtController::class, 'destroy'])->name('courts.destroy');

    // Booking management
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
