<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use App\Models\User;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class AvailableCoursesCommand extends Command
{
    protected string $name = 'availableCourses';
    protected string $description = 'returns list of available courses for user';
    public function handle()
    {
        $user=User::where('telegram_id',$this->getUpdate()->message->from->id)->first();
        $joined_courses=Course::whereHas('globalworks',function ($query) use ($user)
        {
            $query->where('user_id',$user->id);
        })->get();
        $available_courses=Course::all()->diff($joined_courses)->where("course_complete","!=",null);
        $buttons=[];
        if ($available_courses->isNotEmpty()) {
            foreach ($available_courses as $course){
                $buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'1:'.$course->id]]);
            }
        }
        $reply_markup=Keyboard::make(
            ['inline_keyboard'=>$buttons]);
        Telegram::sendMessage([
            'text' => 'bellow there are list of available courses for joining',
            'chat_id'=>$user->telegram_id,
            'reply_markup' => $reply_markup
        ]);
    }
}
