<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Downloads extends Model
{
    protected $fillable=[
        "file_id","path","course_id","question_id","original_name"];
    use HasFactory;
    public function questions(){
        return $this->belongsTo(Question::class);
    }
}
