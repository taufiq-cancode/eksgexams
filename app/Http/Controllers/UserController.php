<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function allUsers()
    {
        try {
    
            $users = User::all();
    
            return response()->json([
                'users' => $users,
            ], 201);

        } catch(\Exception $e){
            return response()->json([
                'message' => 'Error retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    } 
    public function createUser(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|confirmed|string|min:8',
            ]);
    
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
    
            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
            ], 201);

        } catch(\Exception $e){
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }  
    public function updateUser(Request $request, $userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
                'role' => 'sometimes|string|max:255',
                'is_active' => 'sometimes|boolean'
            ]);

            $user->fill($data);
            $user->save();

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ], 200);
            
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
