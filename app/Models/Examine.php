<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examine extends Model
{
    use HasFactory;

    protected $fillable = ["question_id", "user_id", "exam_finished_in", "exam_id", "examine_closure", "examine_expires", "correct_answers_percentage"];

    public function globalworks()
    {
        return $this->hasMany(Globalwork::class);
    }
}
