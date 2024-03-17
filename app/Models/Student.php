<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'exam_type_id',
        'student_code',
        'firstname',
        'surname',
        'othername',
        'date_of_birth',
        'gender',
        'state_of_origin',
        'lga',
        'passport'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function pin()
    {
        return $this->hasOne(StudentPin::class);
    }
}
