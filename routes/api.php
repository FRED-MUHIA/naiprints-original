<?php

use App\Http\Controllers\Api\MpesaController;
use Illuminate\Support\Facades\Route;

Route::prefix('mpesa')->group(function () {
    Route::post('stk-push', [MpesaController::class, 'stkPush']);
    Route::post('callback', [MpesaController::class, 'callback']);
    Route::get('status/{checkoutId}', [MpesaController::class, 'status']);
});
