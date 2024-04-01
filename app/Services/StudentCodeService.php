<?php

namespace App\Services;

use App\Models\School;
use App\Models\Student;

class StudentCodeService
{
    public function updateStudentCodes()
    {
        $schools = School::all();
        $examTypeSuffixes = [
            1 => 'P',
            2 => 'J',
            3 => 'S',
        ];

        foreach ($schools as $school) {
            // Initialize a counter for each exam type within the school
            $counters = [
                1 => 1,
                2 => 1,
                3 => 1,
            ];

            $students = Student::where('school_id', $school->id)
                            ->orderBy('surname')
                            ->get();

            foreach ($students as $student) {
                $examTypeSuffix = $examTypeSuffixes[$student->exam_type_id];
                $orderedNumber = str_pad($counters[$student->exam_type_id], 4, '0', STR_PAD_LEFT);
                $newCode = $school->school_code . $orderedNumber . $examTypeSuffix;

                $student->update(['student_code' => $newCode]);
                $counters[$student->exam_type_id]++;
            }
        }
    }

}
