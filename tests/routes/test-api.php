<?php

use Illuminate\Support\Facades\Route;
use ApurbaLabs\IAM\Tests\Support\Http\Controllers\InvoiceController;

// Routes protected by IAM Middleware
Route::middleware(['api', 'iam:invoice.view'])->group(function () {
    Route::get('/test/invoices', function () {
        return response()->json(['data' => 'success']);
    });
});

// Routes requiring manual Scoped Check inside Controller
Route::middleware(['api'])->group(function () {
    Route::post('/test/invoices/{id}/approve', [InvoiceController::class, 'approve']);
});