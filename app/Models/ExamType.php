<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    use HasFactory;

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'exam_type_subject');
    }

}

