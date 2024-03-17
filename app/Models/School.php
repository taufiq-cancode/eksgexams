<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'school_name',
        'school_code',
        'owner',
        'lg_id'
    ];

    public function localGovernment()
    {
        return $this->belongsTo(LocalGovernment::class, 'lg_id', 'id');
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
