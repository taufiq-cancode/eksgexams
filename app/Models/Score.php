<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_id',
        'subject_id',
        'ca1_score',
        'ca2_score',
    ];
    
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
