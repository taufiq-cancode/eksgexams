<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SchoolController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/user', [UserController::class, 'createUser']);
Route::post('/super-admin/login', [AuthController::class, 'superAdminLogin']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    Route::put('/user/{userId}', [UserController::class, 'updateUser']);

    Route::get('/schools', [SchoolController::class, 'allSchools']);
    Route::post('/schools', [SchoolController::class, 'addSchool']);
    Route::get('/school/{schoolId}', [SchoolController::class, 'viewSchool']);
    Route::put('/school/{schoolId}', [SchoolController::class, 'updateSchool']);
});
