<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
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
                    $query->select('schools.id', 'schools.school_name', 'schools.school_code', 'schools.owner', 'schools.lg_id')
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
            $data = $request->validate([
                'school_name' => 'required|string',
                'lg_id' => 'required|exists:local_governments,id', 
                'exam_type_ids' => 'sometimes|array',
                'exam_type_ids.*' => 'exists:exam_types,id'
            ]);
    
            $schoolCode = $this->generateUniqueSchoolCode();

            $school = School::create([
                'school_name' => $data['school_name'],
                'lg_id' => $data['lg_id'],
                'school_code' => $schoolCode,
            ]);

            if (isset($data['exam_type_ids'])) {
                $school->examTypes()->attach($data['exam_type_ids']);
            }

            $school = School::with('localGovernment', 'examTypes')->find($school->id);

            $transformedSchool = [
                'id' => $school->id,
                'school_name' => $school->school_name,
                'school_code' => $school->school_code,
                'owner' => $school->owner,
                'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
                'exam_types' => $school->examTypes->pluck('name'),
            ];

            return response()->json([
                'message' => 'School added successfully',
                'school' => $transformedSchool
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error adding school',
                'error' => $e->getMessage()
            ]);
        }
    }

    private function generateUniqueSchoolCode()
    {
        do {
            $code = rand(10000000, 99999999); 
        } while (School::where('school_code', $code)->exists());

        return $code;
    }


    public function viewSchool($schoolId)
    {
        $school = School::with('localGovernment', 'pin', 'examTypes')
                        ->find($schoolId);

        if (!$school) {
            return response()->json([
                'message' => 'School not found'
            ], 404);
        }
    
        $transformedSchool = [
            'id' => $school->id,
            'school_name' => $school->school_name,
            'school_code' => $school->school_code,
            'owner' => $school->owner,
            'local_government' => $school->localGovernment ? $school->localGovernment->lg_name : null,
            'pin' => $school->pin ? $school->pin->pin : null,
            'exam_types' => $school->examTypes->pluck('name'),
        ];
    
        return response()->json($transformedSchool);
    }

    public function updateSchool(Request $request, $schoolId)
    {
        $school = School::find($schoolId);

        if (!$school) {
            return response()->json([
                'message' => 'School not found'
            ], 404);
        }

        $data = $request->validate([
            'school_name' => 'sometimes|max:255',
            'owner' => 'sometimes'
        ]);

        $school->fill($data);
        $school->save();

        return response()->json([
            'message' => 'School updated sucessfully',
            'school' => $school
        ]);
    }
   
}
