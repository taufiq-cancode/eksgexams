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
            ['student_id' => 284, 'subject_id' => 49, 'ca1_score' => 13, 'ca2_score' => 15],
            ['student_id' => 280, 'subject_id' => 49, 'ca1_score' => 16, 'ca2_score' => 11],
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
