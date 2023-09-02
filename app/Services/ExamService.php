<?php

namespace App\Services;

use App\Http\Requests\ExamRequest;
use App\Models\Course;
use Illuminate\Support\Collection;

class ExamService
{
    public function examCreate(Course $course):Collection
    {
        $MaxNumber=$course->questions()->count();
        return collect(["course"=>$course,"MaxNumber"=>$MaxNumber]);
    }
    public function examStore(ExamRequest $request,Course $course):void
    {
        $course->exams()->create($request->validated());
    }
}
