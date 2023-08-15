<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Globalwork;
use App\Models\Question;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CourseService
{
    public function courseStore(Request $request):void{
        Course::query()->create(["courseName" => $request->input("courseName")]);
    }
    public function courseEdit(Course $course):Question
    {
        return Question::where("course_id",$course->id)->get();
    }
    public function courseShow(Course $course):Collection
    {
        $exams=$course->exams()->get();
        $courseQuestions=$course->questions();
        $usersQuestions=$course->globalworksGet()->get()->count();
        if ($usersQuestions!=$courseQuestions->count() and $usersQuestions>0) {
            session()->flash("refresh","since you joined course"." ".$course->courseName." "."was updated, there is button bellow to update it for you");
        }
        return collect(["course"=>$course,'questions'=>$usersQuestions,'exams'=>$exams]);
    }
    public function courseOpen(Course $course):void
    {
        $course->update(["course_complete"=>true]);
    }
}
