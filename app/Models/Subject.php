<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    // public function examTypes()
    // {
    //     return $this->belongsToMany(ExamType::class, 'exam_type_subject');
    // }
    
    public function examTypes()
    {
        return $this->belongsToMany(ExamType::class, 'exam_type_subject')
                    ->withPivot('is_compulsory');
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

}
