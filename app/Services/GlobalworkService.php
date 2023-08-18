<?php

namespace App\Services;

use App\Models\{Course,Downloads,Exam,Examine,Globalwork,Question,User};
use App\Http\Requests\AnswersRequest;
use Illuminate\Support\Collection;

class GlobalworkService
{
    public Course $course;
    public Question $question;
    public ?int $examine;
    public Collection $data;
    public function __construct(Globalwork $globalwork)
    {
        $this->course=$globalwork->course()->first();
        $this->question=$globalwork->question()->first();
        $this->examine=$globalwork->examine_id;
    }
    public function GlobalworksGetData():Collection
    {

    }
    private function getDownloads():?Downloads
    {
        return Downloads::where("question_id",$this->question->id)->first();
    }
    /*
         * if adding new data in the array, place it in end of return array
         * */
    public function GlobalworkShowData()
    {
        $file=$this->getDownloads();
        return $this->data=collect(['course'=>$this->course,'question'=>$this->question,'file'=>$file,'examine'=>$this->examine]);
    }
    public function GlobalworkUpdate(AnswersRequest $request):bool
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
}
