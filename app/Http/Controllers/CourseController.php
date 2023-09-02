<?php

namespace App\Http\Controllers;

use App\{Models\Course,Services\CourseService};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{Request,RedirectResponse};

class CourseController extends Controller
{
    public function create():Renderable
    {
        return view("course.coursecreate");
    }
    public function store(Request $request):RedirectResponse
    {
        Course::query()->create(["courseName" => $request->input("courseName")]);
        return redirect("admindashboard");
    }
    public function edit(Course $course):Renderable
    {
        $edit=new CourseService($course);
        $questions=$edit->courseEdit();
        return view("course.courseedit", ["course" => $course,"questions"=>$questions]);
    }
    public function show(Course $course):Renderable
    {
        $courseService = new CourseService($course);
        $data = $courseService->courseShow();
        return view("course.courseshow", ['data'=>$data]);
    }
    public function open(Course $course){
        $courseService = new CourseService($course);
        $courseService->courseOpen();
        return redirect()->route("course.show",$course);
    }
}
