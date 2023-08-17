<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use App\Models\User;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;


class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';
    public function handle()
    {
        $keyboard=[
            Keyboard::inlineButton([['text'=>'available courses','callback_data'=>'available']]),
        ];
        $telegram_user=$this->getUpdate()->getMessage()->from;
        switch ($user=User::where('name',$telegram_user->username)->first()){
            case true:
                $text = 'You already started';
                $joined_courses=Course::whereHas('globalworks',function ($query) use ($user)
                {
                    $query->where('user_id',$user->id);
                })->get(['id','courseName']);
                if ($joined_courses->isNotEmpty()) {
                    $keyboard[] = Keyboard::inlineButton([['text' => 'joined courses','callback_data'=>'joined']]);
                }
                break;
            case false:
                User::create(['name'=>$telegram_user->username,'telegram_id'=>$telegram_user->id]);
                $text='Hey, there! Welcome to our bot!';
                break;
        }
        $reply_markup = Keyboard::make([
            'inline_keyboard' =>$keyboard]);
        Telegram::sendMessage([
            'text' => $text,
            'chat_id'=>$telegram_user->id,
            'reply_markup' => $reply_markup
        ]);
    }
}
