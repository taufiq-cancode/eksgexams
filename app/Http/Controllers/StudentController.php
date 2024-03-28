<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\School;
use App\Models\Score;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function allStudents()
    {
        try {

            $students = Student::with('scores', 'pin', 'examType', 'school')->get();
            
            $transformedStudents = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_code' =>$student->student_code,
                    'firstname' => $student->firstname,
                    'surname' => $student->surname,
                    'othername' => $student->othername,
                    'gender' => $student->gender,
                    'date_of_birth' => $student->date_of_birth,
                    'state_of_origin' => $student->state_of_origin,
                    'local_government' => $student->lga,
                    'passport' => $student->passport,
                    'exam_type' => $student->examType->name ?? null,
                    'pin' => $student->pin ? $student->pin->pin : null,
                    'created_at' => $student->created_at,
                    'school_id' => $student->school_id, 
                    'school_code' => $student->school->school_code,
                    'school_name' => $student->school->school_name,
                    'scores' => $student->scores
                ];
            });
        
        
            return response()->json($transformedStudents);

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving students',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function sortedStudents()
    {
        try {
            $examTypes = ExamType::all()->pluck('name', 'id');
            $result = [];

            foreach ($examTypes as $id => $name) {
                $students = Student::where('exam_type_id', $id)
                    ->with(['school:id,school_name,school_code', 'pin:id,student_id,pin'])
                    ->get()
                    ->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'student_code' =>$student->student_code,
                            'firstname' => $student->firstname,
                            'surname' => $student->surname,
                            'othername' => $student->othername,
                            'gender' => $student->gender,
                            'date_of_birth' => $student->date_of_birth,
                            'state_of_origin' => $student->state_of_origin,
                            'local_government' => $student->lga,
                            'passport' => $student->passport,
                            'exam_type' => $student->examType->name ?? null,
                            'pin' => $student->pin ? $student->pin->pin : null,
                            'created_at' => $student->created_at,
                            'school_id' => $student->school_id, 
                            'school_code' => $student->school->school_code,
                            'school_name' => $student->school->school_name,
                            'scores' => $student->scores
                        ];
                    });

                $result[$name] = [
                    'total' => count($students),
                    'students' => $students
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
            
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving student data.'
            ], 500);
        }
    }

    public function schoolStudents()
    {
        try {
            $schoolId = auth()->user()->id;

            $students = Student::where('school_id', $schoolId)
                        ->with(['examType:id,name', 'pin:id,student_id,pin', 'school:id,school_name,school_code'])
                        ->orderBy('surname') 
                        ->get()
                        ->map(function ($student) {
                            return [
                                'id' => $student->id,
                                'student_code' =>$student->student_code,
                                'firstname' => $student->firstname,
                                'surname' => $student->surname,
                                'othername' => $student->othername,
                                'gender' => $student->gender,
                                'date_of_birth' => $student->date_of_birth,
                                'state_of_origin' => $student->state_of_origin,
                                'local_government' => $student->lga,
                                'passport' => $student->passport,
                                'exam_type' => $student->examType->name ?? null,
                                'pin' => $student->pin ? $student->pin->pin : null,
                                'created_at' => $student->created_at,
                                'school_id' => $student->school_id, 
                                'school_code' => $student->school->school_code,
                                'school_name' => $student->school->school_name,
                                'scores' => $student->scores
                            ];
                        });

            return response()->json([
                'success' => true,
                'data' => $students,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving student data.'
            ], 500);
        }
    }

    public function sortedSchoolStudents()
    {
        try {
            $schoolId = auth()->user()->id;
            $examTypes = ExamType::all()->pluck('name', 'id');
            $result = [];

            foreach ($examTypes as $id => $name) {
                $students = Student::where('school_id', $schoolId)
                            ->where('exam_type_id', $id)
                            ->with(['school:id,school_name,school_code', 'pin:id,student_id,pin', 'scores'])
                            ->orderBy('surname')
                            ->get()
                            ->map(function ($student) {
                                return [
                                    'id' => $student->id,
                                    'student_code' =>$student->student_code,
                                    'firstname' => $student->firstname,
                                    'surname' => $student->surname,
                                    'othername' => $student->othername,
                                    'gender' => $student->gender,
                                    'date_of_birth' => $student->date_of_birth,
                                    'state_of_origin' => $student->state_of_origin,
                                    'local_government' => $student->lga,
                                    'passport' => $student->passport,
                                    'exam_type' => $student->examType->name ?? null,
                                    'pin' => $student->pin ? $student->pin->pin : null,
                                    'created_at' => $student->created_at,
                                    'school_id' => $student->school_id, 
                                    'school_code' => $student->school->school_code,
                                    'school_name' => $student->school->school_name,
                                    'scores' => $student->scores
                                ];
                            });

                $result[$name] = [
                    'total' => count($students),
                    'students' => $students
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving student data.'
            ], 500);
        }
    }

    public function studentsBySchool($schoolId)
    {
        try {
            $students = Student::where('school_id', $schoolId)
                            ->with(['examType:id,name', 'pin:id,student_id,pin'])
                            ->get()
                            ->map(function ($student) {
                                return [
                                    'id' => $student->id,
                                    'student_code' =>$student->student_code,
                                    'firstname' => $student->firstname,
                                    'surname' => $student->surname,
                                    'othername' => $student->othername,
                                    'gender' => $student->gender,
                                    'date_of_birth' => $student->date_of_birth,
                                    'state_of_origin' => $student->state_of_origin,
                                    'local_government' => $student->lga,
                                    'passport' => $student->passport,
                                    'exam_type' => $student->examType->name ?? null,
                                    'pin' => $student->pin ? $student->pin->pin : null,
                                    'created_at' => $student->created_at,
                                    'school_id' => $student->school_id, 
                                    'school_code' => $student->school->school_code,
                                    'school_name' => $student->school->school_name,
                                    'scores' => $student->scores
                                ];
                            });

            return response()->json([
                'success' => true,
                'data' => $students,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving student data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registerStudent(Request $request)
    {
        try {

            $registrationActive = Setting::where('key', 'student_registration_active')->first()->value === 'true';

            if (!$registrationActive) {
                return response()->json([
                    'message' => 'Student registration is currently disabled'
                ], 403);
            }

            DB::beginTransaction();

            $school = auth()->user();

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

            $request->validate([
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
                'school_id' => $school->id,
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
    
    public function viewStudent($studentId)
    {
        $student = Student::with('scores.subject.examTypes', 'pin', 'examType', 'school')
                        ->find($studentId);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        $examTypeId = $student->examType->id ?? null;
    
        $transformedStudent = [
            'id' => $student->id,
            'student_code' =>$student->student_code,
            'firstname' => $student->firstname,
            'surname' => $student->surname,
            'othername' => $student->othername,
            'gender' => $student->gender,
            'date_of_birth' => $student->date_of_birth,
            'state_of_origin' => $student->state_of_origin,
            'local_government' => $student->lga,
            'passport' => $student->passport,
            'exam_type' => $student->examType->name ?? null,
            'pin' => $student->pin ? $student->pin->pin : null,
            'created_at' => $student->created_at,
            'school_id' => $student->school_id, 
            'school_code' => $student->school->school_code,
            'school_name' => $student->school->school_name,
            'scores' => $student->scores->map(function ($score) use ($examTypeId) {
                $isCompulsory = false;
                if ($examTypeId) {
                    $isCompulsory = $score->subject->examTypes->contains(function ($examType) use ($examTypeId) {
                        return $examType->id == $examTypeId && $examType->pivot->is_compulsory;
                    });
                }
    
                return [
                    'id' => $score->id,
                    'student_id' => $score->student_id,
                    'subject_id' => $score->subject_id,
                    'subject_name' => $score->subject->name, 
                    'is_compulsory' => $isCompulsory,
                    'ca1_score' => $score->ca1_score,
                    'ca2_score' => $score->ca2_score,
                ];
            }),
        ];
    
        return response()->json($transformedStudent);
    }
    
    
    public function updateStudent(Request $request, $studentId)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'firstname' => 'sometimes|string',
                'surname' => 'sometimes|string',
                'othername' => 'sometimes|string',
                'date_of_birth' => 'sometimes|date',
                'state_of_origin' => 'sometimes|string',
                'lga' => 'sometimes|string',
                'passport' => 'sometimes|string',
                'ca_scores' => 'sometimes|array',
                'ca_scores.*.id' => 'required_with:ca_scores|exists:scores,id',
                'ca_scores.*.subject_id' => 'required_with:ca_scores|exists:subjects,id',
                'ca_scores.*.ca1_score' => 'nullable|numeric',
                'ca_scores.*.ca2_score' => 'nullable|numeric',
            ]);

            $student = Student::findOrFail($studentId);
            $student->fill($request->only(['firstname', 'surname', 'othername', 'date_of_birth', 'state_of_origin', 'lga', 'passport', 'exam_type_id']));
            $student->save();

            if ($request->has('ca_scores')) {
                foreach ($request->ca_scores as $scoreData) {
                    $score = Score::where('student_id', $studentId)
                                ->where('subject_id', $scoreData['subject_id'])
                                ->first();
                    
                    if ($score) {
                        $score->fill([
                            'ca1_score' => $scoreData['ca1_score'] ?? null,
                            'ca2_score' => $scoreData['ca2_score'] ?? null,
                        ])->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Student and scores updated successfully',
                'student' => $student,
                'scores' => $student->scores
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation error',
                'error' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating student and scores',
                'error' => $e->getMessage(),
            ], 500);
        }
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
}
