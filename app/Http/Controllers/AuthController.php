<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{  
    public function superAdminLogin(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            $user = User::where('email', $request->email)->first();
    
            if (!$user || !Hash::check($request->password, $user->password)){
                return response()->json([
                    'message' => 'Incorrect login credentials'
                ], 401);
            }
    
            if ($user->role !== 'super_admin') {
                return response()->json([
                    'message' => 'Access denied'
                ], 403);
            }

            if ($user->is_active === false){
                return response()->json([
                    'message' => 'Access denied'
                ], 403);
            }
    
            $token = $user->createToken('authToken')->plainTextToken;
    
            return response()->json([
                'message' => 'Logged in successfully',
                'token' => $token,
                'user' => $user,
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error logging in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function schoolAdminLogin(Request $request)
    {
        try {
            $request->validate([
                'school_code' => 'required',
                'pin' => 'required',
                'exam_type' => 'required|exists:exam_types,id',
            ]);
    
            $school = School::with('pin')->where('school_code', $request->school_code)->first();
    
            if (!$school || !$school->pin || $request->pin !== $school->pin->pin) { 
                return response()->json([
                    'message' => 'Incorrect login credentials'
                ], 401);
            }

            if ($school->is_active == false){
                return response()->json([
                    'message' => 'Access denied'
                ], 403);
            }
    
            $token = $school->createToken('authToken')->plainTextToken;

            $transformedSchool = [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'school_code' => $school->school_code,
                'pin_limit' => $school->student_limit,
                'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
                'is_active' => $school->is_active
            ];
        
            return response()->json([            
                'message' => 'Logged in successfully',
                'token' => $token,
                'school' => $transformedSchool,
                'exam_type_id' => $request->exam_type
            ]); 

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error logging in',
                'error' => $e->getMessage()
            ], 500);
        }
           
    }

//     public function studentLogin(Request $request)
// {
//     try {
//         $request->validate([
//             'surname' => 'required',
//             'pin' => 'required',
//         ]);

//         // Find the student by pin
//         $student = Student::with('pin', 'school')->whereHas('pin', function ($query) use ($request) {
//             $query->where('pin', $request->pin);
//         })->first();

//         // Check if student exists and surname matches
//         if (!$student || strtolower($student->surname) !== strtolower($request->surname)) { 
//             return response()->json([
//                 'message' => 'Incorrect login credentials'
//             ], 401);
//         }

//         // Transform student data
//         $transformedStudent = [
//             'id' => $student->id,
//             'student_code' => $student->student_code,
//             'firstname' => $student->firstname,
//             'surname' => $student->surname,
//             'othername' => $student->othername,
//             'gender' => $student->gender,
//             'passport' => $student->passport,
//             'date_of_birth' => $student->date_of_birth,
//             'state_of_origin' => $student->state_of_origin,
//             'local_government' => $student->lga,
//             'ca_scores' => $student->scores->map(function ($score) {
//                 return [
//                     'subject_id' => $score->subject_id,
//                     'subject_name' => $score->subject->name,
//                     'ca1_score' => $score->ca1_score,
//                     'ca2_score' => $score->ca2_score,
//                 ];
//             }),
//             'school' => [
//                 'school_id' => $student->school->id,
//                 'school_name' => $student->school->school_name,
//                 'school_code' => $student->school->school_code,
//                 'school_lg' => $student->school->localGovernment->lg_name,
//             ]
//         ];

//         return response()->json([
//             'message' => 'Logged in successfully',
//             'student' => $transformedStudent
//         ]);

//     } catch(\Exception $e) {
//         return response()->json([
//             'message' => 'Error logging in',
//             'error' => $e->getMessage()
//         ], 401);
//     }
// }


public function studentLogin(Request $request)
{
    try {
        $request->validate([
            'pin' => 'required', // Only validate the pin
        ]);

        // Find the student by pin
        $student = Student::with('school')->whereHas('pin', function ($query) use ($request) {
            $query->where('pin', $request->pin);
        })->first();

        // Check if student exists
        if (!$student) { 
            return response()->json([
                'message' => 'Incorrect login credentials'
            ], 401);
        }

        // Transform student data
                $transformedStudent = [
            'id' => $student->id,
            'student_code' => $student->student_code,
            'firstname' => $student->firstname,
            'surname' => $student->surname,
            'othername' => $student->othername,
            'gender' => $student->gender,
            'passport' => $student->passport,
            'date_of_birth' => $student->date_of_birth,
            'state_of_origin' => $student->state_of_origin,
            'local_government' => $student->lga,
            'ca_scores' => $student->scores->map(function ($score) {
                return [
                    'subject_id' => $score->subject_id,
                    'subject_name' => $score->subject->name,
                    'ca1_score' => $score->ca1_score,
                    'ca2_score' => $score->ca2_score,
                ];
            }),
            'school' => [
                'school_id' => $student->school->id,
                'school_name' => $student->school->school_name,
                'school_code' => $student->school->school_code,
                'school_lg' => $student->school->localGovernment->lg_name,
            ]
        ];


        return response()->json([
            'message' => 'Logged in successfully',
            'student' => $transformedStudent
        ]);

    } catch(\Exception $e) {
        return response()->json([
            'message' => 'Error logging in',
            'error' => $e->getMessage()
        ], 401);
    }
}

    // public function checkStatus(Request $request)
    // {
    //     $school = auth()->user();
        
    //     if (!$school) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $isActive = (bool) $school->is_active;
    //     $registrationSetting = Setting::where('key', 'student_registration_active')->first();

    //     $isRegistrationActive = $registrationSetting && $registrationSetting->value === 'true';

    //     return response()->json([
    //         'is_school_active' => $isActive,
    //         'is_registration_active' => $isRegistrationActive
    //     ]);
    // }
    
    public function checkStatus(Request $request)
    {
        $school = auth()->user();
        $isActive = $school ? (bool) $school->is_active : null; 
        $pin_limit = $school->student_limit;
    
        $registrationSetting = Setting::where('key', 'student_registration_active')->first();
        $isRegistrationActive = $registrationSetting && $registrationSetting->value === 'true';
    
        $responseData = [
            'is_registration_active' => $isRegistrationActive
        ];
    
        if ($school) {
            $responseData['is_school_active'] = $isActive;
            $responseData['pin_limit'] = $pin_limit;
        }
    
        return response()->json($responseData);
    }


  
}
