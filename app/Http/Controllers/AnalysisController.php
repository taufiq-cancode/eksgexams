<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function analysis(){
        $allStudentsCount = Student::where('exam_type_id', 2)->count();

        $privateStudentsCount = DB::table('students')
            ->join('schools', 'students.school_id', '=', 'schools.id')
            ->join('school_exam_type', 'schools.id', '=', 'school_exam_type.school_id')
            ->where('schools.owner', 'private')
            ->where('school_exam_type.exam_type_id', 2)
            ->count();
        
        $govtStudentsCount = DB::table('students')
            ->join('schools', 'students.school_id', '=', 'schools.id')
            ->join('school_exam_type', 'schools.id', '=', 'school_exam_type.school_id')
            ->where('schools.owner', 'government')
            ->where('school_exam_type.exam_type_id', 2)
            ->count();
        
        $maleStudentsCount = Student::where('gender', 'male')->count();
        $femaleStudentsCount = Student::where('gender', 'female')->count();

        $privateMaleStudentsCount = Student::whereHas('school', function ($query) {
            $query->where('owner', 'private');
        })->where('gender', 'male')->count();

        $privateFemaleStudentsCount = Student::whereHas('school', function ($query) {
            $query->where('owner', 'private');
        })->where('gender', 'female')->count();

        $govtMaleStudentsCount = Student::whereHas('school', function ($query) {
            $query->where('owner', 'government');
        })->where('gender', 'male')->count();

        $govtFemaleStudentsCount = Student::whereHas('school', function ($query) {
            $query->where('owner', 'government');
        })->where('gender', 'female')->count();

        return response()->json([
            'jss3_students_count' => $allStudentsCount,
            'private_school_students_count' => $privateStudentsCount,
            'government_school_students_count' => $govtStudentsCount,
            'male_students_count' => $maleStudentsCount,
            'female_students_count' => $femaleStudentsCount,
            'private_male_students_count' => $privateMaleStudentsCount,
            'private_female_students_count' => $privateFemaleStudentsCount,
            'govt_male_students_count' => $govtMaleStudentsCount,
            'govt_female_students_count' => $govtFemaleStudentsCount,
        ]);
    }

    public function quotaAnalysis()
    {
        try {
            $localGovernments = DB::table('local_governments')->get();
    
            $results = [];
            $totalQuota = 0;
            $totalStudentsRegistered = 0;
    
            foreach ($localGovernments as $localGovernment) {
                $totalStudentLimit = $this->calculateTotalStudentLimit($localGovernment->id);
                $studentsCount = $this->getStudentsCount($localGovernment->id);
    
                $totalQuota += $totalStudentLimit;
                $totalStudentsRegistered += $studentsCount;
    
                $results[] = [
                    'lg_name' => $localGovernment->lg_name,
                    'lg_quota' => $totalStudentLimit,
                    'students_registered' => $studentsCount
                ];
            }
    
            $totals = [
                'total_quota_assigned' => $totalQuota,
                'total_students_registered' => $totalStudentsRegistered
            ];
    
            return response()->json([
                'results' => $results,
                'totals' => $totals
            ], 200);
    
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving quota analysis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateTotalStudentLimit($lg_id)
    {
        $schools = DB::table('schools')->where('lg_id', $lg_id)->get();

        $totalStudentLimit = 0;

        foreach ($schools as $school) {
            $currentStudentLimit = $school->student_limit;

            $enrolledStudentsCount = DB::table('students')->where('school_id', $school->id)->count();

            $totalStudentLimit += ($currentStudentLimit + $enrolledStudentsCount);
        }

        return $totalStudentLimit;
    }

    private function getStudentsCount($lg_id)
    {
        $schools = DB::table('schools')->where('lg_id', $lg_id)->get();

        $studentsCount = 0;

        foreach ($schools as $school) {
            $studentsCount += DB::table('students')->where('school_id', $school->id)->count();
        }

        return $studentsCount;
    }

}
