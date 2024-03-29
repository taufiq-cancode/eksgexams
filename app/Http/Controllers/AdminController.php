<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
    
    public function checkStatus(Request $request)
    {
        $registrationSetting = Setting::where('key', 'student_registration_active')->first();

        $isRegistrationActive = $registrationSetting && $registrationSetting->value === 'true';

        return response()->json([
            'is_registration_active' => $isRegistrationActive
        ]);
    }

    public function registerStudent(Request $request)
    {
        try {

            DB::beginTransaction();

            $admin = auth()->user();

            $request->validate([
                'school_id' => 'required|exists:schools,id',
                'firstname' => 'required|string',
                'surname' => 'required|string',
                'othername' => 'nullable|string',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female',
                'state_of_origin' => 'required|string',
                'lga' => 'required|string',
                'passport' => 'nullable|string',
                'exam_type_id' => 'required|exists:exam_types,id',
                'ca_scores' => 'required|array',
                'placed_school_id' => 'nullable',
                'placed_school_lga' => 'nullable|string'
            ]);

            $school = School::findOrFail($request->school_id);

            if ($school->student_limit <= 0) {
                return response()->json([
                    'message' => 'Student registration limit reached. No more students can be registered.',
                ], 403);             
            }

            if ($school->is_active == false) {
                return response()->json([
                    'message' => 'Your school has been deactivated.',
                ], 403);             
            }

            $existingStudent = Student::where('firstname', $request->firstname)
                ->where('surname', $request->surname)
                ->where('othername', $request->othername)
                ->where('date_of_birth', $request->date_of_birth)
                ->where('school_id', $school->id)
                ->where('exam_type_id', $request->exam_type_id)
                ->first();

            if ($existingStudent) {
                return response()->json([
                    'message' => 'A student with similar details already exists.',
                ], 409);
            }

            $student = Student::create([
                'admin_id' => $admin->id,
                'school_id' => $request->school_id,
                'student_code' => "temp",
                'firstname' => $request->firstname,
                'surname' => $request->surname,
                'othername' => $request->othername,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'state_of_origin' => $request->state_of_origin,
                'lga' => $request->lga,
                'passport' => $request->passport,
                'exam_type_id' => $request->exam_type_id,
                'placed_school_id' => $request->placed_school_id,
                'placed_school_lga' => $request->placed_school_lga,
            ]);

            $student_code = $school->school_code . $student->id;
            $student->update(['student_code' => $student_code]);

            $this->generatePinForStudent($student->id, $student_code);

            $createdScores = [];
            foreach ($request->ca_scores as $score) {
                $subjectId = $score['subject_id'];
                $ca1 = $score['ca1_score'] ?? null;
                $ca2 = $score['ca2_score'] ?? null;

                $subject = Subject::find($subjectId);
                if ($subject && $subject->examTypes->contains($request->exam_type_id)) {
                    if ($ca1 !== null && $ca2 !== null) {
                        $createdScore = Score::create([
                            'student_id' => $student->id,
                            'subject_id' => $subjectId,
                            'ca1_score' => $ca1,
                            'ca2_score' => $ca2,
                        ]);
                        $createdScores[] = $createdScore->toArray();
                    }
                }
            }

            $school->decrement('student_limit');

            DB::commit();

            return response()->json([
                'message' => 'Student registered successfully',
                'student' => $student,
                'scores' => $createdScores
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation error',
                'error' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error registering student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generatePinForStudent($studentId, $studentCode)
    {
        $pin = Str::random(6);
        while (DB::table('student_pins')->where('pin', $pin)->exists()) {
            $pin = Str::random(6);
        }
        DB::table('student_pins')->insert([
            'student_id' => $studentId,
            'student_code' => $studentCode,
            'pin' => $pin
        ]);
    }
    public function deleteStudent($studentId)
    {
        try {
            DB::beginTransaction();

            $student = Student::findOrFail($studentId);

            Score::where('student_id', $studentId)->delete();

            $student->delete();

            DB::commit();

            return response()->json([
                'message' => 'Student and scores deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting student and scores',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
