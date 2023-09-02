<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Globalwork extends Model
{
    protected $fillable=["examine_id","course_id","question_id","user_id","userAnswer","course_complete","courseName","num_attempts"];
    use HasFactory;
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function messages()
    {
        return $this->hasMany(Chat_message::class);
    }
    public function chain()
    {
        return $this->hasOne(Chain::class);
    }
}
