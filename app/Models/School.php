<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_code',
        'owner',
        'lg_id'
    ];

    public function localGovernment()
    {
        return $this->belongsTo(LocalGovernment::class, 'lg_id');
    }

    public function pin()
    {
        return $this->hasOne(Pin::class);
    }

    public function examTypes()
    {
        return $this->belongsToMany(ExamType::class, 'school_exam_type', 'school_id', 'exam_type_id');
    }

}