<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentWebController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/student/login', [StudentWebController::class, 'showLogin'])->name('student.login');
Route::post('/student/login', [StudentWebController::class, 'login'])->name('student.login.post');
Route::post('/student/logout', [StudentWebController::class, 'logout'])->name('student.logout');

Route::middleware('web')->group(function () {
    Route::get('/student/dashboard', [StudentWebController::class, 'dashboard'])->name('student.dashboard');
    Route::post('/student/checkin/qr', [StudentWebController::class, 'qrCheckIn'])->name('student.checkin.qr');
    Route::post('/student/checkin/image', [StudentWebController::class, 'imageCheckIn'])->name('student.checkin.image');
});


Route::get('/', function () {
    return view('welcome');
});
