<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswersRequest;
use App\Models\{Course,Question};
use App\Services\GlobalworkService;
use Illuminate\{Contracts\Support\Renderable,Http\RedirectResponse,Support\Facades\Redirect};

class GlobalworkController extends Controller
{
    public function show(Course $course):Renderable
    {
        $Globalwork= new GlobalworkService();
        $data=$Globalwork->GlobalworkShowData($course);
        if ($data['question']->question_type=="test") {
            return view("questions.showTest",['data'=>$data]);
        }
        else {
            return view("questions.showWriting",['data'=>$data]);
        }
    }
    public function Update(AnswersRequest $request,Question $question,int $examine=null):RedirectResponse
    {
        $Globalwork = new GlobalworkService();
        $answer=$Globalwork->GlobalworkUpdate($request,$question,$examine);
        if ($answer) {
            return Redirect::back()->with("message","Good job! you may proceed");
        }
        else {
            return Redirect::back()->with("message","Answer is not correct");
        }
    }
    public function Create(Course $course):RedirectResponse
    {
        $join=new GlobalworkService();
        $join->GlobalworkCreate($course);
        return redirect()->route("course.show",$course);
    }
    public function Refresh(Course $course){
        $course->courseRefresh();
        $this->Create($course);
        session()->flash("message","You have successful refreshed course"." ".$course->courseName." "."there are questions of this course bellow");
        return redirect()->route("course.show",$course);
    }
}
