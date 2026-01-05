<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login',[AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum', 'throttle:5,1')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/office-location', [AttendanceController::class, 'office']);
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkin']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    // Route::get('/attendance/history', [AttendanceController::class, 'history']);
});