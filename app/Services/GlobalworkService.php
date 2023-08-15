<?php

namespace App\Services;

use App\Models\{Course,Downloads,Exam,Examine,Globalwork,Question,User};
use App\Http\Requests\AnswersRequest;

class GlobalworkService
{
    /*
         * if adding new data in the array, place it in end of return array
         * */
    public function GlobalworkShowData(Course $course):\Illuminate\Support\Collection
    {
        $examine=User::find(auth()->user()->id)->currectExamine()->first("id");
        if (!isset($examine->id)) {
            $examine=null;
        } else {
            $examine = $examine->id;
        }
        $globalworks=$course->globalworksGet($examine)->paginate(1);
        $question=$globalworks->items()[0]->question()->first();
        $file=Downloads::where("question_id",$question->id)->first();
        return collect(['course'=>$course,'question'=>$question,'globalworks'=>$globalworks,'file'=>$file,'examine'=>$examine]);
    }
    public function GlobalworkUpdate(AnswersRequest $request,Question $question,int $examine=null):bool
    {
        if ($request->answer == $question->correct_answer){
            $question->userAnswer($examine)->increment("num_attempts",1,
                [
                    "user_answer"=>"correct",
                    "answer_check"=>true
                ]);
            $totalAttempts=$question->globalworks()->sum("num_attempts");
            Question::find($question->id)->update(["total_attempts"=>$totalAttempts]);
            //если exam то без сообщения сделано во вьюхе
            return true;
        }
        else {
            $question->userAnswer($examine)->increment("num_attempts",1,["user_answer"=>$request->answer]);
            $totalAttempts=$question->globalworks()->sum("num_attempts");
            Question::find($question->id)->update(["total_attempts"=>$totalAttempts]);
            return false;
        }
    }
    public function GlobalworkCreate(Course $course,Exam $exam=null,Examine $examine=null):void
    {
        $query=[];
        if ($exam!= null and $examine !=null){
            $questions = $course->questions()->get()->modelKeys();
            $exams_questionsID = array_rand(array_flip($questions), $exam->questions_num);
            foreach ($exams_questionsID as $exam_question) {
                $query[] = ["question_id" => $exam_question,
                    "course_id"=>$course->id,
                    "user_id" => $examine->user_id,
                    "examine_id" => $examine->id,
                    "created_at" => now(),
                    "updated_at" => now()];
            }
        }
        else {
            $table=$course->questions()->get();
            session()->flash("message","You have successful joined course"." ".$course->courseName." "."there are questions of this course bellow");
            foreach ($table as $entities)
            {
                $query[]=["question_id"=>$entities->id,
                    "course_id"=>$entities->course_id,
                    "user_id"=>auth()->id()];
            }
        }
        Globalwork::insert($query);
    }

}
