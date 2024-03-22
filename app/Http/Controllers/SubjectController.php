<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\Subject;
use Illuminate\Http\Request;

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
    
    

}
