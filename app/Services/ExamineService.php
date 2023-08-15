<?php

namespace App\Services;

use App\Events\ExamineStartEvent;
use App\Models\{Course,Exam};
use Illuminate\Support\Collection;

class ExamineService
{
    public function examineCreate(Course $course,Exam $exam):void
    {
        $examine=$exam->examines()->create(["user_id"=>\auth()->user()->id,
            "examine_expires"=>$seconds=now()->addMinutes($exam->minutes_for_exam)]);
        event(new ExamineStartEvent($examine->id,$seconds->diffInSeconds()));
        $globalworkService=new GlobalworkService();
        $globalworkService->GlobalworkCreate($course,$exam,$examine);
    }
    public function examineResults(Exam $exam):Collection
    {
        $examinesInfo=[];
        $examines=$exam->examines()->get();
        foreach ($examines as $examine){
            $globalworks[]=$examine->globalworks()->get();
            $examinesInfo[]=collect($examine)->concat($globalworks);
        }
        return collect(["examines"=>$examinesInfo]);
    }
}
