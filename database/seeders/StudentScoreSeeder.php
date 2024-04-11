<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Score;

class StudentScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $scoreData = [
            ['student_id' => 37, 'subject_id' => 1, 'ca1_score' => 15, 'ca2_score' => 17],
            ['student_id' => 104, 'subject_id' => 2, 'ca1_score' => 18, 'ca2_score' => 16],
            ['student_id' => 82, 'subject_id' => 20, 'ca1_score' => 14, 'ca2_score' => 19],
            ['student_id' => 2, 'subject_id' => 48, 'ca1_score' => 16, 'ca2_score' => 18],
        ];
        
        foreach ($scoreData as $data) {
            $score = new Score();
            
            $score->student_id = $data['student_id'];
            $score->subject_id = $data['subject_id'];
            $score->ca1_score = $data['ca1_score'];
            $score->ca2_score = $data['ca2_score'];
            
            $score->save();
        }
    }
}
