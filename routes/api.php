<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Api\AttendanceController;

Route::post('/student/login', [StudentAuthController::class, 'login']);

Route::post('/attendance/image', [AttendanceController::class, 'imageCheckIn']);

Route::get('/ping', function () { return ['status' => 'ok']; });
