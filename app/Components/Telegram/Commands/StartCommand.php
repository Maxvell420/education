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
        $user=$this->getUpdate()->message->from;
        $name = $user->username??$user->firstName;
        User::create(['name'=>$name, 'telegram_id'=>$user->id]);
        Telegram::sendMessage([
            'text' => 'Привет! Слева от поля ввода сообщения есть кнопка menu, там ты найдешь команды',
            'chat_id'=>$user->id,
        ]);
    }
}
