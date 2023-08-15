<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Downloads;
use App\Models\User;

class GlobalworkService
{
    /*
         * if adding new data in the array, place it in end of return array
         * */
    public function globalworkShow(Course $course){
        $examine=User::find(auth()->user()->id)->currectExamine()->first("id");
        if (!isset($examine->id)){
            $examine=null;
        }
        else $examine=$examine->id;
        $globalworks=$course->globalworksGet($examine)->paginate(1);
        $question=$globalworks->items()[0]->question()->first();
        $file=Downloads::query()->where("question_id",$question->id)->first();
        return collect(['course'=>$course,'question'=>$question,'globalworks'=>$globalworks,'file'=>$file,'examine'=>$examine]);
    }

}
