<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamRequest;
use App\Models\Course;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;

class ExamController extends Controller
{
    public function Create(Course $course)
    {
        $examService = new ExamService();
        $data=$examService->examCreate($course);
        return view("course.examCreate",['data'=>$data]);
    }
    public function Store(ExamRequest $request,Course $course):RedirectResponse
    {
        $examService = new ExamService();
        $examService->examStore($request,$course);
        return redirect("admindashboard");
    }
}
