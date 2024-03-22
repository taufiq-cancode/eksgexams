<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\LocalGovernment;
use App\Models\Pin;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SchoolController extends Controller
{

    public function allSchools()
    {
        try {
            $schools = School::with('localGovernment', 'pin')->get();

            $transformedSchools = $schools->map(function ($school) {
                return [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'school_code' => $school->school_code,
                    'pin_limit' => $school->student_limit,
                    'owner' => $school->owner,
                    'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
                    'pin' => $school->pin ? $school->pin->pin : null,
                ];
            });

            return response()->json($transformedSchools);

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving schools',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sortedSchools()
    {
        try {
            $examTypes = ['CE', 'JSS3', 'SS2'];
            $result = [];

            foreach ($examTypes as $type) {
                $examType = ExamType::where('name', $type)->with(['schools' => function($query) {
                    $query->select('schools.id', 'schools.school_name', 'schools.school_code', 'schools.owner', 'schools.lg_id', 'schools.student_limit')
                              ->with(['localGovernment:id,lg_name', 'pin:id,school_id,pin']);

                }])->first();
                

                if ($examType) {
                    $result[$type] = [
                        'total' => count($examType->schools),
                        'schools' => $examType->schools->map(function ($school) {
                            return [
                                'id' => $school->id,
                                'school_name' => $school->school_name,
                                'school_code' => $school->school_code,
                                'pin_limit' => $school->student_limit,
                                'owner' => $school->owner,
                                'local_government' => $school->localGovernment->lg_name ?? null,
                                'pin' => $school->pin->pin ?? null,
                            ];
                        }),
                    ];
                } else {
                    $result[$type] = [
                        'total' => 0,
                        'schools' => [],
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving data.'
            ], 500);
        }
    }

    public function addSchool(Request $request)
    {
        try {
            DB::beginTransaction();

                $data = $request->validate([
                    'school_name' => 'required|string',
                    'school_code' => 'required|unique:schools,school_code',
                    'school_pin' => 'required|unique:pins,pin,',
                    'pin_limit' => 'nullable|integer',
                    'lg_id' => 'required|exists:local_governments,id', 
                    'owner' => 'required|in:private,government',
                    'exam_type_id' => 'required|integer|exists:exam_types,id',
                ]);

                $school = School::create([
                    'school_name' => $data['school_name'],
                    'lg_id' => $data['lg_id'],
                    'school_code' => $data['school_code'],
                    'student_limit' => $data['pin_limit'] ?? null,
                ]);

                $pin = Pin::create([
                    'school_id' => $school->id,
                    'school_code' => $school->school_code,
                    'pin' => $request->school_pin
                ]);

                DB::table('school_exam_type')->insert([
                    'school_id' => $school->id,
                    'exam_type_id' => $request->exam_type_id
                ]);

                if (isset($data['exam_type_ids'])) {
                    $school->examTypes()->attach($data['exam_type_ids']);
                }

                $school = School::with('localGovernment', 'examTypes')->find($school->id);

                $transformedSchool = [
                    'id' => $school->id,
                    'school_name' => $school->school_name,
                    'school_code' => $school->school_code,
                    'pin_limit' => $school->student_limit,
                    'owner' => $school->owner,
                    'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
                    'exam_types' => $school->examTypes->pluck('name'),
                ];

            DB::commit();

            return response()->json([
                'message' => 'School added successfully',
                'school' => $transformedSchool,
                'pin' => $pin
            ]);

        } catch(\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error adding school',
                'error' => $e->getMessage()
            ]);
        }
    }

    // private function generateUniqueSchoolCode()
    // {
    //     do {
    //         $code = rand(10000000, 99999999); 
    //     } while (School::where('school_code', $code)->exists());

    //     return $code;
    // }


    public function viewSchool($schoolId)
    {
        try {
            $school = School::with([
                'localGovernment', 
                'pin', 
                'examTypes' => function ($query) use ($schoolId) {
                    $query->with(['students' => function ($query) use ($schoolId) {
                        $query->where('school_id', $schoolId)
                            ->with('pin');
                    }]);
                }
            ])->find($schoolId);

            if (!$school) {
                return response()->json([
                    'message' => 'School not found'
                ], 404);
            }

            $transformedSchool = [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'school_code' => $school->school_code,
                'pin' => $school->pin ? $school->pin->pin : null,
                'pin_limit' => $school->student_limit,
                'owner' => $school->owner,
                'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
                'exam_types' => $school->examTypes->map(function($examType) {
                    return [
                        'exam_type' => $examType->name,
                        'students' => $examType->students
                    ];
                }),
            ];

            return response()->json($transformedSchool);

        } catch(\Exception $e) {
            \Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving data.'
            ], 500);
        }
        
    }

    public function updateSchool(Request $request, $schoolId)
    {
        try {
            DB::beginTransaction();
    
            $school = School::find($schoolId);
    
            if (!$school) {
                return response()->json([
                    'message' => 'School not found'
                ], 404);
            }
    
            $data = $request->validate([
                'school_name' => 'sometimes|string',
                'school_code' => 'sometimes|unique:schools,school_code,'.$schoolId,
                'school_pin' => 'sometimes|unique:pins,pin,'.$school->pin->id,
                'pin_limit' => 'sometimes|integer',
                'lg_id' => 'sometimes|exists:local_governments,id', 
                'owner' => 'sometimes|in:private,government',
                'exam_type_id' => 'sometimes|integer|exists:exam_types,id',
                'is_active' => 'sometimes|boolean'
            ]);
    
            $updateData = [];
    
            foreach ($data as $key => $value) {
                if ($request->filled($key)) {
                    if ($key == 'pin_limit') {
                        $updateData['student_limit'] = $value;
                    } else {
                        $updateData[$key] = $value;
                    }
                }
            }
    
            $school->update($updateData);
    
            if ($request->filled('school_pin') && $school->pin && $school->pin->pin != $request->school_pin) {
                $school->pin->update(['pin' => $request->school_pin]);
            }
    
            if ($request->filled('exam_type_id')) {
                $school->examTypes()->sync([$request->exam_type_id]);
            }
    
            $school = School::with('localGovernment', 'examTypes')->find($school->id);
    
            $transformedSchool = [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'school_code' => $school->school_code,
                'pin_limit' => $school->student_limit,
                'owner' => $school->owner,
                'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
                'exam_types' => $school->examTypes->pluck('name'),
                'is_active' => $school->is_active
            ];
    
            DB::commit();
    
            return response()->json([
                'message' => 'School updated successfully',
                'school' => $transformedSchool
            ]);
    
        } catch(\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Error updating school',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function lgaSchools()
    {
        $localGovernments = LocalGovernment::with(['schools' => function($query) {
            $query->whereHas('examTypes', function($q) {
                $q->where('id', 2);
            });
        }])->get();

        $transformedLocalGovernments = $localGovernments->map(function ($localGovernment) {
            return [
                'id' => $localGovernment->id,
                'code' => $localGovernment->lg_code,
                'name' => $localGovernment->lg_name,
                'schools' => $localGovernment->schools->map(function ($school) {
                    return [
                        'school_id' => $school->id,
                        'owner' => $school->owner,
                        'name' => $school->school_name,
                        'school_code' => $school->school_code,
                    ];
                })
            ];
        });

        return response()->json($transformedLocalGovernments);


    }

    public function generateBroadSheet(Request $request, $schoolId)
    {
        $school = School::with(['students', 'students.scores.subject'])->find($schoolId);

        if (!$school) {
            return response()->json(['message' => 'School not found'], 404);
        }

        $broadSheet = $school->students->map(function ($student) {
            return [
                'student_id' => $student->id,
                'student_code' => $student->student_code,
                'firstname' => $student->firstname,
                'othername' => $student->othername,
                'surname' => $student->surname,
                'gender' => $student->gender,
                'state_of_origin' => $student->state_of_origin,
                'lga' => $student->lga,
                'date_of_birth' => $student->date_of_birth,
                'scores' => $student->scores->map(function ($score) {
                    return [
                        'subject' => $score->subject->name,
                        'ca1_score' => $score->ca1_score,
                        'ca_score' => $score->ca1_score,
                    ];
                })
            ];
        });

        $examTypes = $school->examTypes->pluck('name')->join(', ');

        $schoolDetails = [
            'id' => $school->id,
            'name' => $school->school_name,
            'school_code' => $school->school_code,
            'exam_type' => $examTypes,
            'local_government' => $school->localGovernment->lg_name,
        ];

        return response()->json([
            'school' => $schoolDetails,
            'broad_sheet' => $broadSheet
        ]);
    }

}
