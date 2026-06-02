<?php

use App\Http\Controllers\Api\MpesaController;
use Illuminate\Support\Facades\Route;

Route::prefix('mpesa')->group(function () {
    Route::post('stk-push', [MpesaController::class, 'stkPush'])
        ->middleware(['api.key', 'throttle:10,1']);
    Route::post('callback', [MpesaController::class, 'callback'])
        ->middleware('throttle:120,1');
    Route::get('status/{checkoutId}', [MpesaController::class, 'status'])
        ->middleware(['api.key', 'throttle:30,1']);
});
