<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswersRequest;
use App\Models\{Course, Examine, Globalwork, Question, User};
use App\Services\GlobalworkService;
use Illuminate\{Contracts\Support\Renderable, Http\RedirectResponse, Support\Collection, Support\Facades\Redirect};
use Illuminate\Http\Request;

class GlobalworkController extends Controller
{
    public function show(Request $request,Course $course)
    {
        $examine=$request->request->get('examine');
        $query=$course->globalworksGet($examine!=null?$examine: null);
        $globalworks=$query->paginate(1);
        $globalworkService= new GlobalworkService($globalworks->items()[0]);
        $data = $globalworkService->GlobalworkShowData();
        return view("questions.showTest",['globalworks'=>$globalworks,'data'=>$data]);
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
    public function Create(Course $course,  Examine $examine=null, Collection $questions = null):RedirectResponse
    {
        if ($questions == null)   {
            $questions = $course->questions()->get();
            session()->flash("message","You have successful joined course"." ".$course->courseName." "."there are questions of this course bellow");
        }
        foreach ($questions as $question) {
                $question->globalworks()->create([
                'user_id'=>auth()->user()->id,
                "course_id"=>$course->id,
                "examine_id" => $examine?->id]);}
        return redirect()->route("course.show",$course);
    }
    public function Refresh(Course $course):RedirectResponse
    {
        $course->courseRefresh();
        $this->Create($course);
        session()->flash("message","You have successful refreshed course"." ".$course->courseName." "."there are questions of this course bellow");
        return redirect()->route("course.show",$course);
    }
}
