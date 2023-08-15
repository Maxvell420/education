<?php

namespace App\Http\Controllers;

use App\{Models\Course,Services\CourseService};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{Request,RedirectResponse};

class CourseController extends Controller
{
    public function Create():Renderable
    {
        return view("course.coursecreate");
    }
    public function Store(Request $request):RedirectResponse
    {
        $course=new CourseService();
        $course->courseStore($request);
        return redirect("admindashboard");
    }
    public function Edit(Course $course):Renderable
    {
        $edit=new CourseService();
        $questions=$edit->courseEdit($course);
        return view("course.courseedit", ["course" => $course,"questions"=>$questions]);
    }
    public function Show(Course $course):Renderable
    {
        $courseService = new CourseService();
        $data = $courseService->courseShow($course);
        return view("course.courseshow", ['data'=>$data]);
    }
    public function Open(Course $course){
        $courseService = new CourseService();
        $courseService->courseOpen($course);
        return redirect()->route("course.show",$course);
    }
}
