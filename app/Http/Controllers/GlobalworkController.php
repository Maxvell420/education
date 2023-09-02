<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswersRequest;
use App\Services\CourseService;
use App\Services\UserService;
use App\Models\{Course, Examine, Globalwork, Question, User};
use App\Services\GlobalworkService;
use Illuminate\{Contracts\Support\Renderable,
    Http\RedirectResponse,
    Support\Collection,
    Support\Facades\Redirect,
    Validation\ValidationException};
use Illuminate\Http\Request;

class GlobalworkController extends Controller
{
    public function show(Request $request,Course $course)
    {
        $examine=$request->request->get('examine');
        $query=$course->globalworksGet($examine);
        $globalworks=$query->paginate(1);
        $globalworkService= new GlobalworkService($globalworks->items()[0]);
        $data = $globalworkService->GlobalworkShowData();
        return view("questions.showTest",['globalworks'=>$globalworks,'data'=>$data]);
    }
    public function Update(Request $request,Globalwork $globalwork):RedirectResponse
    {
        $Globalwork = new GlobalworkService($globalwork);
        $Globalwork->GlobalworkUpdate($request->get('answer'));
        if ($globalwork->answer_check) {
            return Redirect::back()->with("message","Good job! you may proceed");
        }
        else {
            return Redirect::back()->with("message","Answer is not correct");
        }
    }
    public function Create(Course $course,  Examine $examine=null, Collection $questions = null):RedirectResponse
    {
        $userService = new UserService(auth()->user()->id);
        $userService->courseJoin($course,$examine,$questions);
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
