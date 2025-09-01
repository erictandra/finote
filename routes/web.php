<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('report')->name('reports.')->group(function () {
    Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions');
});
