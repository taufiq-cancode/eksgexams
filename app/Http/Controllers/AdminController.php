<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function toggleStudentReg(Request $request)
    {
        $request->validate(['is_active' => 'required|boolean']);

        $setting = Setting::where('key', 'student_registration_active')->first();
        $setting->value = $request->is_active ? 'true' : 'false';
        $setting->save();

        return response()->json(['message' => 'Student registration setting updated']);
    }
}
