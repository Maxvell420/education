<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Examine;
use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CourseService
{
    public Collection $questions;
    public Course $course;
    public ?Collection $exams;
    public function __construct(Course $course)
    {
        $this->course=$course;
        $this->questions=$course->questions()->get();
        $this->exams=$course->exams()->get();
    }
    public function courseEdit():Collection
    {
        return Question::where("course_id",$this->course->id)->get();
    }
    public function courseShow():Collection
//    Переделать методы с использованием свойств класса
    {
        $exams=$this->course->exams()->get();
        $usersQuestions=$this->course->globalworksGet()->get()->count();
        return collect(["course"=>$this->course,'questions'=>$usersQuestions,'exams'=>$exams]);
    }
    public function courseOpen():void
    {
        $this->course->update(["course_complete"=>true]);
    }
}
