<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\School;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
                    'pin' => $student->pin ? $student->pin->pin : null,
                    'scores' => $student->scores
                ];
            });
        
        
            return response()->json($transformedStudents);

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving students',
                'error' => $e->getMessage()
            ]);
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
                            'student_code' => $student->student_code,
                            'name' => $student->firstname . ' ' . $student->surname . ' ' . $student->othername,
                            'date_of_birth' => $student->date_of_birth,
                            'gender' => $student->gender,
                            'school' => $student->school->school_name ?? null,
                            'school_code' => $student->school->school_code,
                            'pin' => $student->pin->pin ?? null,
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
                                'student_code' => $student->student_code,
                                'name' => $student->firstname . ' ' . $student->surname . ' ' . $student->othername,
                                'exam_type' => $student->examType->name ?? null,
                                'pin' => $student->pin->pin ?? null,
                                'school_id' => $student->school_id, 
                                'school_code' => $student->school->school_code,
                                'school_name' => $student->school->school_name
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
                            ->with(['school:id,school_name,school_code', 'pin:id,student_id,pin'])
                            ->orderBy('surname')
                            ->get()
                            ->map(function ($student) {
                                return [
                                    'student_code' => $student->student_code,
                                    'name' => $student->firstname . ' ' . $student->surname . ' ' . $student->othername,
                                    'date_of_birth' => $student->date_of_birth,
                                    'gender' => $student->gender,
                                    'school' => $student->school->school_name ?? null,
                                    'school_code' => $student->school->school_code,
                                    'pin' => $student->pin->pin ?? null,
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
                                    'student_code' => $student->student_code,
                                    'name' => $student->firstname . ' ' . $student->surname . ' ' . $student->othername,
                                    'exam_type' => $student->examType->name ?? null,
                                    'pin' => $student->pin->pin ?? null,
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
            DB::beginTransaction();

            $request->validate([
                'firstname' => 'required|string',
                'surname' => 'required|string',
                'othername' => 'nullable|string',
                'date_of_birth' => 'nullable|date',
                'state_of_origin' => 'required|string',
                'lga' => 'required|string',
                'passport' => 'nullable|string',
                'exam_type_id' => 'required|exists:exam_types,id',
                'ca_scores' => 'required|array'
            ]);

            $school = auth()->user();

            $existingStudent = Student::where('firstname', $request->firstname)
                ->where('surname', $request->surname)
                ->where('othername', $request->othername)
                ->where('date_of_birth', $request->date_of_birth)
                ->where('school_id', $school->id)
                ->first();

            if ($existingStudent) {
                return response()->json([
                    'message' => 'A student with similar details already exists.',
                ], 409);
            }

            $student_code = $this->generateUniqueStudentCode();

            $student = Student::create([
                'school_id' => $school->id,
                'student_code' => $student_code,
                'firstname' => $request->firstname,
                'surname' => $request->surname,
                'othername' => $request->othername,
                'date_of_birth' => $request->date_of_birth,
                'state_of_origin' => $request->state_of_origin,
                'lga' => $request->lga,
                'passport' => $request->passport,
                'exam_type_id' => $request->exam_type_id,
            ]);

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

            DB::commit();

            return response()->json([
                'message' => 'Student registered successfully',
                'student' => $student,
                'scores' => $createdScores
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error registering student',
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    public function viewStudent($studentId)
    {
        $student = Student::with('scores', 'pin', 'examType', 'school')
                        ->find($studentId);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }
    
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
            'pin' => $student->pin ? $student->pin->pin : null,
            'scores' => $student->scores
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
    
    private function generateUniqueStudentCode()
    {
        do {
            $code = rand(10000000, 99999999); 
        } while (Student::where('student_code', $code)->exists());

        return $code;
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
