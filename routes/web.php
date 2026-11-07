<?php

use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\OnlineBookingController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// SaaS Platform Public Marketing Pages
Route::get('/', function () {
    return view('saas.home');
})->name('saas.home');

Route::get('/features', function () {
    return view('saas.features');
})->name('saas.features');

Route::get('/pricing', function () {
    return view('saas.pricing');
})->name('saas.pricing');

// Live Salon Public Experience (Glow & Grace)
Route::get('/salon', function () {
    return view('tenant.home');
})->name('tenant.home');

Route::get('/salon/services', function () {
    return view('tenant.services');
})->name('tenant.services');

// Interactive Online Booking Engine
Route::get('/salon/booking', [OnlineBookingController::class, 'index'])->name('booking.index');
Route::get('/salon/booking/slots', [OnlineBookingController::class, 'getSlots'])->name('booking.slots');
Route::post('/salon/booking', [OnlineBookingController::class, 'store'])->name('booking.store');
Route::get('/salon/booking/confirmed/{reference}', [OnlineBookingController::class, 'confirm'])->name('booking.confirm');

// Client Self-Service Portal
Route::get('/portal', [CustomerPortalController::class, 'index'])->name('portal.dashboard');
Route::get('/portal/invoice/{invoice}/download', [CustomerPortalController::class, 'downloadInvoice'])->name('portal.invoice.download');

// Demo Switcher Route for local exploration
Route::get('/demo/login/{role}', function (string $role) {
    $email = match ($role) {
        'admin' => 'platform@example.test',
        'owner' => 'owner@glowgrace.test',
        'reception' => 'reception@glowgrace.test',
        'stylist' => 'stylist@glowgrace.test',
        'customer' => 'customer@example.test',
        default => 'owner@glowgrace.test',
    };

    $user = User::where('email', $email)->first();
    if ($user) {
        Auth::login($user);
        $redirectUrl = match ($role) {
            'admin' => '/admin',
            'owner' => '/app',
            'reception' => '/reception',
            'stylist' => '/staff',
            'customer' => '/portal',
            default => '/app',
        };
        return redirect($redirectUrl);
    }

    return redirect('/');
})->name('demo.login');
