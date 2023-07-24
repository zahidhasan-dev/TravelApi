<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use App\Http\Controllers\Api\V1\Admin\TravelController as AdminTravelControler;
use App\Http\Controllers\Api\V1\Admin\TourController as AdminTourControler;


Route::prefix('v1')->group(function () {
    Route::get('travels', [TravelController::class, 'index']);
    Route::get('travels/{travel:slug}/tours', [TourController::class, 'index']);
    Route::post('login', LoginController::class);
    
    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::post('travels', [AdminTravelControler::class, 'store']);
            Route::post('travels/{travel}/tours', [AdminTourControler::class, 'store']);
        });

        Route::middleware('role:admin,editor')->group(function () {
            Route::get('travels', [AdminTravelControler::class, 'index']);
            Route::put('travels/{travel}', [AdminTravelControler::class, 'update']);
            Route::get('travels/{travel}/tours', [AdminTourControler::class, 'index']);
            Route::put('travels/{travel}/tours/{tour}', [AdminTourControler::class, 'update']);
        });
    });
});
