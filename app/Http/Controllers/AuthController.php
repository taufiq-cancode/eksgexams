<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\School;
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
    
            $token = $user->createToken('authToken')->plainTextToken;
    
            return response()->json([
                'message' => 'Logged in successfully',
                'token' => $token
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function schoolAdminLogin(Request $request)
    {
        try {
            $request->validate([
                'school_code' => 'required',
                'pin' => 'required',
            ]);
    
            $school = School::with('pin')->where('school_code', $request->school_code)->first();
    
            if (!$school || !$school->pin || $request->pin !== $school->pin->pin) { 
                throw ValidationException::withMessages([
                    'school_code' => ['The provided credentials are incorrect.'],
                ]);
            }
    
            $token = $school->createToken('authToken')->plainTextToken;

            $transformedSchool = [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'school_code' => $school->school_code,
                'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
                'exam_types' => $school->examTypes->pluck('name')
            ];
        
            return response()->json([            
                'message' => 'Logged in successfully',
                'token' => $token,
                'school' => $transformedSchool
            ]); 

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error logging in',
                'error' => $e->getMessage()
            ]);
        }
           
    }

  
}
