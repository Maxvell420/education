<?php

namespace App\Services;

use App\Events\ChainCreateEvent;
use App\Models\Chain;
use App\Models\Course;
use App\Models\Globalwork;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class BotService extends UserService

{
    public ?array $buttons ;
    public Update $update;
    public ?string $data = null;
    public string $text;
    public ?Keyboard $keyboard;
    public function __construct(Update $update)
    {
        $this->update=$update;
        $this->data=$this->update->callbackQuery?->data;
        $user=$this->checkCallbackData($update);
        parent::__construct($user);
    }
    public function checkCallbackData($update):User
    {
        if ($this->data == null) {
            return User::where('telegram_id',$this->update->message->from->id)->first();
        }
        else return User::where('telegram_id',$this->update->callbackQuery->from->id)->first();
    }
    public function getMessageFrom(): \Telegram\Bot\Objects\User
    {
        return $this->update->message->from;
    }
    public function CourseData(Course $course):CourseService
    {
        return new CourseService($course);
    }
    public function getUser():User
    {
        return $this->user;
    }
//    public function checkGlobalwoksInCourse(Course $course):void
//    {
//        $globalworks=$course->getGlobalwokrs($this->user->id);
//        foreach ($globalworks->get() as $globalwork)
//        {
//            if ($globalwork->answer_check)
//            {
//                {
//                    $this->buttons[]=Keyboard::inlineButton([[
//                        'text' => 'continue this course',
//                        'callback_data'=>$course->id.':'.$globalwork->id]]);
//                }
//            }
//        }
//        $this->buttons??$globalworks->first();
//    }
    public function showCourse(Course $course):void
    {
        $globalworks = $course->getUsersGlobalworks($this->user->id)->get();
        if ($globalworks->isEmpty()){
            $this->text = $course->course_info;
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => 'click this button for joining this course',
                'callback_data'=>'2'.':'.$course->id]]);
        }
        else {
            $this->text = 'choose button bellow';
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => 'question where you have stopped',
                'callback_data'=>'3'.':'.$globalworks->where('answer_check','=',false)->min('id')]]);
            $x=0;
                foreach ($globalworks as $globalwork)
                    {
                        $this->buttons[]=Keyboard::inlineButton([[
                            'text' => $x++,
                            'callback_data'=>'3'.':'.$globalwork->id]]);
                    }
            }
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons]);
    }
    public function sendMessage(?string $text=null):Message
    {
        if (isset($this->keyboard)){
            return Telegram::sendMessage([
                'text' => $text??$this->text,
                'chat_id'=>$this->user->telegram_id,
                'reply_markup' => $this->keyboard
            ]);
        }
        return Telegram::sendMessage([
            'text' => $text??$this->text,
            'chat_id'=>$this->user->telegram_id,
        ]);
    }
    public function registerUserInCourse(Course $course):void
    {
        $collection=$this->courseJoin($course);
        $this->text = $collection['text']?
            'You have successful joined course'. ' ' . $course->courseName:
            'You already have joined '. $course->courseName;
        $this->buttons[]=Keyboard::inlineButton([[
            'text' => 'proceed joined course',
            'callback_data'=>'3'.':'.$collection['globalwork']]]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons]);

    }
    private function ChainCreate(Model $model):Chain
    {
        $chain = $model->chain()->firstOrCreate(['user_id'=>$this->user->id],['user_id'=>$this->user->id,class_basename($model).'_id'=>$model->id]);
        if ($chain->wasRecentlyCreated){
            event(new ChainCreateEvent($this,$chain));
        }
        $this->buttons=[Keyboard::button([['text'=>'/exit']])];
        return $chain;
    }
    public function exitMode()
    {
        $this->user->chain()->delete();
        $this->keyboard = Keyboard::remove(['remove_keyboard'=>true]);
        $this->text='you have exited current mode';
    }
    public function getGlobalworkQuestionData(int $globalwork_id):void
    {
        $globalwork=Globalwork::find($globalwork_id);
        $globalworkService= new GlobalworkService($globalwork);
        $this->ChainCreate($globalwork);
        $question=$globalworkService->GlobalworkShowData()['question'];
        array_push($this->buttons,
            Keyboard::button([['text'=>$question->answer_4]]),
            Keyboard::button([['text'=>$question->answer_3]]),
            Keyboard::button([['text'=>$question->answer_2]]),
            Keyboard::button([['text'=>$question->answer_1]])
        );
        $this->text=$question->problem;
        $this->keyboard=Keyboard::make(['keyboard'=>array_reverse($this->buttons),'resize_keyboard'=>true]);
    }
    public function user_answer_check(bool $flag, $chain):void
    {
        if ($flag) {
            $this->sendMessage('Good job! Answer is correct, now sending to you next question');
            $globalwork=Globalwork::where('user_id',$this->user->id)->where('answer_check',false)->first();
            if ($globalwork!=null) {
                $chain->update(['globalwork_id'=>$globalwork->id]);
                $this->getGlobalworkQuestionData($chain->globalwork_id);
            }
            else {
                $this->text = 'it seem you have completed this course, good job';
                Telegram::triggerCommand('exit',$this->update);
            }
        }
        else {
            $this->text = 'Answer is not correct, try again';
        }
    }
    public function unknown(){
        $this->text = 'i dont understand';
    }
}
