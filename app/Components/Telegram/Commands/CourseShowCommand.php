<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use App\Models\User;
use App\Services\CourseService;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class CourseShowCommand extends Command
{
    protected string $name = 'courseShow';
    protected string $description = 'returns list of joined courses for user';
    public function handle()
    {
        $update=$this->getUpdate();
        $user=User::where('telegram_id',$update->message->from->id)->first();
        $course=Course::find(substr($update->callbackQuery->data,2));
        $courseService = new CourseService();
        $data = $courseService->courseShow($course);
        if ($data['questions']==0)  {
            $buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'2:'.$course->id]]);
            $text='join this course';
        }
        else {
            for($x=1;$x<=$data['questions']->count();$x++){
                $buttons[]=Keyboard::inlineButton([['text'=>$x,'callback_data'=>'3:'.$x]]);
                $text='questions of this course';
            }
        }
        $reply_markup=Keyboard::make(
            ['inline_keyboard'=>$buttons]);
        Telegram::sendMessage([
            'text' => $text,
            'chat_id'=>$user->telegram_id,
            'reply_markup' => $reply_markup
        ]);
    }
}
