<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Downloads extends Model
{
    protected $fillable=[
        "original_name","given_name","path","course_id","question_id"];
    use HasFactory;
    public function questions(){
        return $this->belongsTo(Question::class);
    }
}
