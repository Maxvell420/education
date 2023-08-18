<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use App\Models\User;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class JoinedCoursesCommand extends Command
{
    protected string $name = 'joinedCourses';
    protected string $description = 'returns list of joined courses for user';
    public function Handle()
    {
        $user=User::where('telegram_id',$this->getUpdate()->message->from->id)->first();
        $joined_courses=Course::whereHas('globalworks',function ($query) use ($user)
        {
            $query->where('user_id',$user->id);
        })->get();
        if ($joined_courses->isNotEmpty()) {
            foreach ($joined_courses as $course){
                $buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'1:'.$course->id]]);
            }
            $reply_markup=Keyboard::make(
                ['inline_keyboard'=>$buttons]);
            Telegram::sendMessage([
                'text' => 'bellow there are list of joined courses for joining',
                'chat_id'=>$user->telegram_id,
                'reply_markup' => $reply_markup
            ]);
        }
        else {
            Telegram::sendMessage([
                'text' => 'you have not joined any courses yet',
                'chat_id'=>$user->telegram_id,
            ]);
        }

    }
}
