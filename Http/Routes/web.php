<?php

use Illuminate\Support\Facades\Route;
use Modules\SPPassport\Http\Controllers\Frontend\IndexController;
use Modules\SPPassport\Http\Controllers\Frontend\CompareController;
use Modules\SPPassport\Http\Controllers\Frontend\FlightSearchController;

// Alle Routen unter /passport, nur für eingeloggte Benutzer
Route::middleware(['web', 'auth'])->group(function () {

    // /passport/
    Route::get('/', [IndexController::class, 'index'])
        ->name('index');

    // /passport/compare/{user}
    Route::get('/compare/{user}', [CompareController::class, 'show'])
        ->whereNumber('user')
        ->name('compare');

    // /passport/flights/{country}
    Route::get('/flights/{country}', [FlightSearchController::class, 'searchByCountry'])
        ->whereAlpha('country')
        ->name('flights.country');
});
