<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\School;
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

    public function studentLogin(Request $request)
    {
        try {
            $request->validate([
                'student_code' => 'required',
                'pin' => 'required',
            ]);
    
            $student = Student::with('pin')->where('student_code', $request->student_code)->first();
    
            if (!$student || !$student->pin || $request->pin !== $student->pin->pin) { 
                return response()->json([
                    'message' => 'Incorrect login credentials'
                ], 401);
            }
    
            $transformedStudent = [
                'id' => $student->id,
                'student_code' => $student->student_code,
                'firstname' => $student->firstname,
                'surname' => $student->surname,
                'othername' => $student->othername,
                'date_of_birth' => $student->date_of_birth,
                'state_of_origin' => $student->state_of_origin,
                'local_government' => $student->lga,
                'ca_scores' => $student->scores
            ];
        
            return response()->json([            
                'message' => 'Logged in successfully',
                'student' => $transformedStudent
            ]); 

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error logging in',
                'error' => $e->getMessage()
            ]);
        }
           
    }

  
}
