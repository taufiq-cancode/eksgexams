<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentController;


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
Route::post('/school-admin/login', [AuthController::class, 'schoolAdminLogin']);
Route::post('/student/login', [AuthController::class, 'studentLogin']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    Route::put('/user/{userId}', [UserController::class, 'updateUser']);
    
    // SUBJECT ROUTES
    Route::get('/schools', [SchoolController::class, 'allSchools']);
    Route::get('/sorted-schools', [SchoolController::class, 'sortedSchools']);
    Route::post('/schools', [SchoolController::class, 'addSchool']);
    Route::get('/school/{schoolId}', [SchoolController::class, 'viewSchool']);
    Route::put('/school/{schoolId}', [SchoolController::class, 'updateSchool']);

    // SUBJECT ROUTES
    Route::post('/exam-type/add-subject', [SubjectController::class, 'addSubjectToExamType']);

    // STUDENT ROUTES
    Route::get('/students', [StudentController::class, 'allStudents']);
    Route::get('/students/school/{schoolId}', [StudentController::class, 'studentsBySchool']);
    Route::get('/student/{studentId}', [StudentController::class, 'viewStudent']);
    Route::get('/sorted-students', [StudentController::class, 'sortedStudents']);


});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/students', [StudentController::class, 'registerStudent']);
    Route::get('/school-students', [StudentController::class, 'schoolStudents']);
    Route::get('/sorted-school-students', [StudentController::class, 'sortedSchoolStudents']);
    Route::get('/student/{studentId}', [StudentController::class, 'viewStudent']);
    Route::put('/student/{studentId}', [StudentController::class, 'updateStudent']);
    Route::delete('/student/{studentId}', [StudentController::class, 'deleteStudent']);

});
