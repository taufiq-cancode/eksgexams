<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;


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
  
}
