<?php

namespace App\Http\Controllers;

use App\Events\ExamineStartEvent;
use App\Models\{Course,Exam};
use App\Services\ExamineService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;

class ExamineController extends Controller
{
    public function create(Course $course,Exam $exam):RedirectResponse
    {
        $examineService=new ExamineService();
        $examineService->examineCreate($course,$exam);
        return redirect()->route("globalworks.show",[$course]);
    }
    public function results(Exam $exam):Renderable
    {
        $examineService = new ExamineService();
        $data=$examineService->examineResults($exam);
        return view("course.ExamineResults",['data'=>$data]);
    }
    public function end(Course $course,int $examine):RedirectResponse
    {
        event(new ExamineStartEvent($examine));
        return \redirect()->route("course.show",[$course]);
    }
}
