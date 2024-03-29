<?php

namespace App\Services;

use App\Models\School;
use App\Models\Student;

class StudentCodeService
{
    public function generateStudentCodes()
    {
        $schools = School::all();

        foreach ($schools as $school) {
            $students = Student::where('school_id', $school->id)->orderBy('surname')->get();
            $count = 0;

            foreach ($students as $student) {
                $count++; 

                $studentCodeSuffix = $this->getStudentCodeSuffix($student->exam_type_id);
                $studentCode = $school->school_code . '/' . str_pad($count, 4, '0', STR_PAD_LEFT) . $studentCodeSuffix;
                $student->update(['student_code' => $studentCode]);
            }
        }
    }

    private function getStudentCodeSuffix($examTypeId)
    {
        $suffixes = [
            1 => 'P',
            2 => 'J', 
            3 => 'S', 
        ];

        return $suffixes[$examTypeId] ?? '';
    }
}
