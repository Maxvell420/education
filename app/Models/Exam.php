<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable=["questions_num","minutes_for_exam"];
    use HasFactory;
    public function examines(){
        return $this->hasMany(Examine::class);
    }
    public function course(){
        return $this->belongsTo(Course::class);
    }
}
