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

    public function getAnalysisByExamtype($examTypeId) 
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

    public function getAnalysisByLGA($lgaId) 
    {
        $lga = LocalGovernment::find($lgaId);

        if (!$lga) {
            return response()->json(['message' => 'Local Government Area not found'], 404);
        }

        $response = [
            'id' => $lga->id,
            'lg_name' => $lga->lg_name,
        ];

        $examTypes = ExamType::with('subjects')->get();
        foreach ($examTypes as $examType) {
            $schoolsData = [];

            foreach ($lga->schools as $school) {
                if ($school->examTypes->contains($examType->id)) {
                    $subjectsData = [];

                    foreach ($examType->subjects as $subject) {
                        $studentCount = $school->students()
                            ->whereHas('scores', function ($query) use ($subject) {
                                $query->where('subject_id', $subject->id);
                            })->count();

                        $subjectsData[] = [
                            'id' => $subject->id,
                            'name' => $subject->name,
                            'student_count' => $studentCount,
                        ];
                    }

                    $schoolsData[] = [
                        'id' => $school->id,
                        'school_name' => $school->school_name,
                        'subjects' => $subjectsData
                    ];
                }
            }

            if (!empty($schoolsData)) {
                $response[$examType->name] = $schoolsData;
            }
        }

        return response()->json($response);
    }

    public function getAnalysisBySchool($schoolId) 
    {
        $school = School::with('examTypes.subjects')->find($schoolId);

        if (!$school) {
            return response()->json(['message' => 'School not found'], 404);
        }

        $examTypesData = $school->examTypes->map(function ($examType) use ($school) {
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
                'id' => $examType->id,
                'name' => $examType->name,
                'subjects' => $subjectsData
            ];
        });

        return response()->json([
            'id' => $school->id,
            'school_name' => $school->school_name,
            'examTypes' => $examTypesData
        ]);
    }

    public function getAnalysisByLGAs($examTypeId) 
    {
        $lgas = LocalGovernment::all();
        $response = [];

        foreach ($lgas as $lga) {
            $subjectsData = Subject::whereHas('examTypes', function ($query) use ($examTypeId) {
                $query->where('exam_type_id', $examTypeId);
            })->get()->map(function ($subject) use ($lga, $examTypeId) {
                $studentCount = $subject->scores()->whereHas('student', function ($query) use ($lga, $examTypeId) {
                    $query->where('exam_type_id', $examTypeId)
                          ->whereHas('school', function ($schoolQuery) use ($lga) {
                              $schoolQuery->where('lg_id', $lga->id);
                          });
                })->count();

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'student_count' => $studentCount
                ];
            });

            $response[] = [
                'id' => $lga->id,
                'lg_name' => $lga->lg_name,
                'subjects' => $subjectsData
            ];
        }

        return response()->json($response);
    }

}
