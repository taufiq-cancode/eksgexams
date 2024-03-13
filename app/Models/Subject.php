<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    public function examTypes()
    {
        return $this->belongsToMany(ExamType::class, 'exam_type_subject');
    }

}
