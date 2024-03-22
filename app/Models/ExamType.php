<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    use HasFactory;

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'exam_type_subject')
                    ->withPivot('is_compulsory');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_exam_type', 'exam_type_id', 'school_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'exam_type_id');
    }
    
    

}

