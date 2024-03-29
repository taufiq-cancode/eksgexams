<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\LocalGovernment;
use App\Models\School;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

set_time_limit(300);

class SubjectController extends Controller
{
    public function allSubjects()
    {
        $subjects = Subject::all()->makeHidden(['created_at', 'updated_at']);

        return response()->json($subjects);
    }
    
    public function sortedSubjects()
    {
        $examTypes = ExamType::with(['subjects' => function ($query) {
            $query->withPivot('is_compulsory');
        }])->get();

        $result = $examTypes->map(function ($examType) {
            return [
                'exam_type' => $examType->name,
                'subjects' => $examType->subjects->map(function ($subject) {
                    return [
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'is_compulsory' => $subject->pivot->is_compulsory
                    ];
                }),
            ];
        });

        return response()->json($result);
    }
    
    public function addSubjectToExamType(Request $request)
    {
        $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'subject_id' => 'required|exists:subjects,id', 
            'is_compulsory' => 'sometimes|boolean'
        ]);

        $examType = ExamType::find($request->exam_type_id);
        $subjectId = $request->subject_id;
        $isCompulsory = $request->input('is_compulsory', false); 

        $examType->subjects()->attach($subjectId, ['is_compulsory' => $isCompulsory]);

        return response()->json(['message' => 'Subject added to Exam Type successfully']);
    } 

    // public function getAnalysis()
    // {
    //     $lgas = LocalGovernment::with('schools.examTypes')->get();

    //     $lgaData = $lgas->map(function ($lga) {
    //         $examTypesData = $this->getExamTypesDataForLGA($lga->id);
    //         $schoolsData = $lga->schools->map(function ($school) {
    //             return [
    //                 'id' => $school->id,
    //                 'school_name' => $school->school_name,
    //                 'examTypes' => $school->examTypes->map(function ($examType) use ($school) {
    //                     return [
    //                         'id' => $examType->id,
    //                         'name' => $examType->name,
    //                         'subjects' => $this->getSubjectsDataForSchoolAndExamType($school->id, $examType->id)
    //                     ];
    //                 })
    //             ];
    //         });

    //         return [
    //             'id' => $lga->id,
    //             'lg_name' => $lga->lg_name,
    //             'examTypes' => $examTypesData,
    //             'schools' => $schoolsData
    //         ];
    //     });

    //     return response()->json($lgaData);
    // }

    // private function getExamTypesDataForLGA($lgaId)
    // {
    //     // Fetch all exam types
    //     $examTypes = ExamType::all();

    //     // Process each exam type to get its subjects and student counts
    //     return $examTypes->map(function ($examType) use ($lgaId) {
    //         $subjects = Subject::select('subjects.id', 'subjects.name')
    //                         ->join('exam_type_subject', 'subjects.id', '=', 'exam_type_subject.subject_id')
    //                         ->where('exam_type_subject.exam_type_id', $examType->id)
    //                         ->get();

    //         // Count students for each subject in this LGA
    //         $subjects->map(function ($subject) use ($lgaId, $examType) {
    //             $studentCount = Student::whereHas('scores', function ($query) use ($subject) {
    //                                 $query->where('subject_id', $subject->id);
    //                             })
    //                             ->whereHas('school', function ($query) use ($lgaId) {
    //                                 $query->where('lg_id', $lgaId);
    //                             })
    //                             ->where('exam_type_id', $examType->id)
    //                             ->count();
                
    //             $subject->student_count = $studentCount;
    //             return $subject;
    //         });

    //         return [
    //             'id' => $examType->id,
    //             'name' => $examType->name,
    //             'subjects' => $subjects
    //         ];
    //     });
    // }

    // private function getSubjectsDataForSchoolAndExamType($schoolId, $examTypeId)
    // {
    //     $subjects = Subject::select('subjects.id', 'subjects.name')
    //                     ->join('exam_type_subject', 'subjects.id', '=', 'exam_type_subject.subject_id')
    //                     ->where('exam_type_subject.exam_type_id', $examTypeId)
    //                     ->get();

    //     // Count students for each subject in this school and exam type
    //     $subjects->map(function ($subject) use ($schoolId) {
    //         $studentCount = Student::whereHas('scores', function ($query) use ($subject) {
    //                             $query->where('subject_id', $subject->id);
    //                         })
    //                         ->where('school_id', $schoolId)
    //                         ->count();

    //         $subject->student_count = $studentCount;
    //         return $subject;
    //     });

    //     return $subjects;
    // }



    // private function getExamTypesData($lgaId)
    // {
    //     $examTypes = ExamType::all();
    //     $examTypesData = [];

    //     foreach ($examTypes as $examType) {
    //         $subjectsData = $this->getSubjectsDataForLgaAndExamType($lgaId, $examType->id);

    //         array_push($examTypesData, [
    //             'id' => $examType->id,
    //             'name' => $examType->name,
    //             'subjects' => $subjectsData,
    //         ]);
    //     }

    //     return $examTypesData;
    // }

    // private function getSubjectsDataForLgaAndExamType($lgaId, $examTypeId)
    // {
    //     $subjects = Subject::all();
    //     $subjectsData = [];

    //     foreach ($subjects as $subject) {
    //         $studentCount = $this->getStudentCountForSubjectLgaAndExamType($subject->id, $lgaId, $examTypeId);

    //         array_push($subjectsData, [
    //             'id' => $subject->id,
    //             'name' => $subject->name,
    //             'student_count' => $studentCount,
    //         ]);
    //     }

    //     return $subjectsData;
    // }

    // private function getStudentCountForSubjectLgaAndExamType($subjectId, $lgaId, $examTypeId)
    // {
    //     $count = Student::join('schools', 'students.school_id', '=', 'schools.id')
    //                 ->join('scores', 'students.id', '=', 'scores.student_id')
    //                 ->where('scores.subject_id', $subjectId)
    //                 ->where('schools.lg_id', $lgaId)
    //                 ->where('students.exam_type_id', $examTypeId)
    //                 ->count();

    //     return $count;
    // }

    // private function getSchoolsData($lgaId)
    // {
    //     $schools = LocalGovernment::find($lgaId)->schools;
    //     $schoolsData = [];

    //     foreach ($schools as $school) {
    //         $examTypesData = $this->getSchoolExamTypesData($school->id);

    //         $schoolData = [
    //             'id' => $school->id,
    //             'school_name' => $school->school_name,
    //             'examTypes' => $examTypesData,
    //         ];

    //         array_push($schoolsData, $schoolData);
    //     }

    //     return $schoolsData;
    // }

    // private function getSchoolExamTypesData($schoolId)
    // {
    //     $examTypes = ExamType::all();
    //     $examTypesData = [];

    //     foreach ($examTypes as $examType) {
    //         $subjectsData = $this->getSubjectsDataForSchoolAndExamType($schoolId, $examType->id);

    //         array_push($examTypesData, [
    //             'id' => $examType->id,
    //             'name' => $examType->name,
    //             'subjects' => $subjectsData,
    //         ]);
    //     }

    //     return $examTypesData;
    // }

    // private function getSubjectsDataForSchoolAndExamType($schoolId, $examTypeId)
    // {
    //     $subjects = Subject::all();
    //     $subjectsData = [];

    //     foreach ($subjects as $subject) {
    //         $studentCount = Student::join('scores', 'students.id', '=', 'scores.student_id')
    //                             ->where('students.school_id', $schoolId)
    //                             ->where('students.exam_type_id', $examTypeId)
    //                             ->where('scores.subject_id', $subject->id)
    //                             ->count();

    //         array_push($subjectsData, [
    //             'id' => $subject->id,
    //             'name' => $subject->name,
    //             'student_count' => $studentCount,
    //         ]);
    //     }

    //     return $subjectsData;
    // }


    // public function getAnalysis()
    // {
    //     try {
    //         $lgas = LocalGovernment::all();
    //         $data = [];

    //         foreach ($lgas as $lga) {
    //             $lgaData = [
    //                 'id' => $lga->id,
    //                 'lg_name' => $lga->lg_name,
    //                 'examTypes' => $this->getExamTypesData($lga->id),
    //                 'schools' => $this->getSchoolsData($lga->id)
    //             ];

    //             array_push($data, $lgaData);
    //         }

    //         return response()->json($data);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    //     }
    // }

    // private function getExamTypesData($lgaId)
    // {
    //     $examTypes = ExamType::all();
    //     $examTypesData = [];

    //     foreach ($examTypes as $examType) {
    //         $subjectsData = [];

    //         foreach ($examType->subjects as $subject) {
    //             $studentCount = $this->getStudentCountForSubjectInLGA($subject->id, $examType->id, $lgaId);
    //             array_push($subjectsData, [
    //                 'id' => $subject->id,
    //                 'name' => $subject->name,
    //                 'student_count' => $studentCount
    //             ]);
    //         }

    //         array_push($examTypesData, [
    //             'id' => $examType->id,
    //             'name' => $examType->name,
    //             'subjects' => $subjectsData
    //         ]);
    //     }

    //     return $examTypesData;
    // }

    // private function getSchoolsData($lgaId)
    // {
    //     $schools = LocalGovernment::find($lgaId)->schools;
    //     $schoolsData = [];
    
    //     foreach ($schools as $school) {
    //         $schoolData = [
    //             'id' => $school->id,
    //             'school_name' => $school->school_name,
    //             'examTypes' => $this->getSchoolExamTypeData($school->id)
    //         ];
    
    //         array_push($schoolsData, $schoolData);
    //     }
    
    //     return $schoolsData;
    // }

    // private function getSchoolExamTypeData($schoolId)
    // {
    //     $examTypes = School::find($schoolId)->examTypes;
    //     $examTypesData = [];

    //     foreach ($examTypes as $examType) {
    //         $subjectsData = [];

    //         foreach ($examType->subjects as $subject) {
    //             $studentCount = $this->getStudentCountForSubjectInSchool($subject->id, $examType->id, $schoolId);
    //             array_push($subjectsData, [
    //                 'id' => $subject->id,
    //                 'name' => $subject->name,
    //                 'student_count' => $studentCount
    //             ]);
    //         }

    //         array_push($examTypesData, [
    //             'id' => $examType->id,
    //             'name' => $examType->name,
    //             'subjects' => $subjectsData
    //         ]);
    //     }

    //     return $examTypesData;
    // }

    // private function getStudentCountForSubjectInSchool($subjectId, $examTypeId, $schoolId)
    // {
    //     // Implement the logic to count students for a subject in a specific school and Exam Type
    //     // Example query (adjust according to your actual database schema)
    //     $count = DB::table('students')
    //                 ->join('scores', 'students.id', '=', 'scores.student_id')
    //                 ->where('scores.subject_id', $subjectId)
    //                 ->where('students.school_id', $schoolId)
    //                 ->where('students.exam_type_id', $examTypeId)
    //                 ->count();

    //     return $count;
    // }

    // private function getStudentCountForSubjectInLGA($subjectId, $examTypeId, $lgaId)
    // {
    //     // Implement the logic to count students for a subject in a specific LGA and Exam Type
    //     // Example query (adjust according to your actual database schema)
    //     $count = DB::table('students')
    //                 ->join('scores', 'students.id', '=', 'scores.student_id')
    //                 ->where('scores.subject_id', $subjectId)
    //                 ->where('students.lga', $lgaId)
    //                 ->where('students.exam_type_id', $examTypeId)
    //                 ->count();

    //     return $count;
    // }

    public function getAnalysis($examTypeId) 
    {
        $examType = ExamType::with('subjects')->find($examTypeId);

        if (!$examType) {
            return response()->json(['message' => 'Exam type not found'], 404);
        }

        $lgas = LocalGovernment::with(['schools' => function ($query) use ($examTypeId) {
            $query->whereHas('examTypes', function ($query) use ($examTypeId) {
                $query->where('exam_types.id', $examTypeId);
            });
        }])->get();

        $data = $lgas->map(function ($lga) use ($examType) {
            $schoolsData = $lga->schools->map(function ($school) use ($examType) {
                $subjectsData = $examType->subjects->map(function ($subject) use ($school) {
                    $studentCount = $school->students()
                        ->whereHas('scores', function ($query) use ($subject) {
                            $query->where('subject_id', $subject->id);
                        })->count();

                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'student_count' => $studentCount,
                    ];
                });

                return [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'subjects' => $subjectsData
                ];
            });

            return [
                'id' => $lga->id,
                'lg_name' => $lga->lg_name,
                'schools' => $schoolsData
            ];
        });

        return response()->json($data);
    }

}
