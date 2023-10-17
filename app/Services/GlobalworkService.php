<?php

namespace App\Services;

use App\Models\{Chain, Course, Downloads, Exam, Examine, Globalwork, Question, User};
use App\Http\Requests\AnswersRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class GlobalworkService
{
    public Globalwork $globalwork;
    public Course $course;
    public Question $question;
    public ?int $examine;
    public Collection $data;
    public string $correct_answer;
    public function __construct(Globalwork $globalwork)
    {
        $this->course=$globalwork->course()->first();
        $this->question=$globalwork->question()->first();
        $this->examine=$globalwork->examine_id;
        $this->correct_answer=$this->question->toArray()[$this->question->correct_answer];
        $this->globalwork=$globalwork;
    }
    private function getDownloads():?Downloads
    {
        return $this->question->downloads()->first();
    }
    public function GlobalworkShowData():Collection
    {
        $download=$this->getDownloads();
        return $this->data=collect(['course'=>$this->course,'Название'=>$this->question,'file'=>$download,'examine'=>$this->examine]);
    }
    public function GlobalworkUpdate(string $user_answer):void
    {
        $param = $user_answer== $this->correct_answer ?
            [
                "user_answer"=>"correct",
                "answer_check"=>true
            ]
            :
            [
                "user_answer" => 'incorrect',
                "answer_check"=>false
            ];
            $this->globalwork->increment("num_attempts",1,$param);
            $this->globalwork->save();
        $this->question->increment("num_attempts");
    }
    public function answerCheck():string
    {
        return $this->globalwork->answer_check;
    }
}
