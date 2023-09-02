<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use App\Models\User;
use App\Services\BotService;
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
            Keyboard::Button([['text'=>'/available_courses']]),
        ];
        $user=$this->getUpdate()->message->from;
        $name = $user->username??$user->firstName;
        switch (User::where('telegram_id',$user->id)->get()->isNotEmpty()){
            case true:
                $botService = new BotService($this->getUpdate());
                if ($botService->joinedCourses()->isNotEmpty()){
                    $keyboard[] = Keyboard::Button([['text' => '/joined_courses']]);
                }
                $text = 'You already started';
                break;
            case false:
                User::create(['name'=>$name, 'telegram_id'=>$user->id]);
                $text='Hey, there! Welcome to our bot!';
                break;
        }
        $reply_markup = Keyboard::make([
            'keyboard' =>$keyboard,
            'is_persistent'=>true]);
        Telegram::sendMessage([
            'text' => $text,
            'chat_id'=>$user->id,
            'reply_markup' => $reply_markup
        ]);
    }
}
