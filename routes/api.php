<?php

use App\Http\Controllers\CourtController;
use Illuminate\Support\Facades\Route;

/**
 * Phase 3 - Task T008: API Routes for Real-Time Booking Validation
 * 
 * These routes provide AJAX endpoints for dynamic form validation
 */

// Get available durations for a selected start time (US1 - Pre-Booking Validation)
Route::get('/courts/{court}/availability/durations', [CourtController::class, 'getAvailableDurations'])
    ->name('api.courts.availability.durations');
