<?php

namespace Database\Seeders;

use App\Models\ExamType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamTypeSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $examTypesWithSubjects = [
            1 => ['subject' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43], 'compulsory' => [1, 2, 5]],
            2 => ['subject' => [1, 2, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54], 'compulsory' => [1, 2, 44, 45, 46, 47, 48, 49]],
            3 => ['subject' => [1, 2, 48, 55, 56], 'compulsory' => [1, 2, 48, 55, 56]],
        ];

        foreach ($examTypesWithSubjects as $examTypeId => $data) {
            $examType = ExamType::find($examTypeId);

            foreach ($data['subject'] as $subjectId) {
                $isCompulsory = in_array($subjectId, $data['compulsory']);
                $examType->subjects()->attach($subjectId, ['is_compulsory' => $isCompulsory]);
            }
        }
    }
}
