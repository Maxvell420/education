<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use App\Models\User;
use App\Services\BotService;
use App\Services\botv2\MessageHandler;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;


class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';
    public function handle()
    {
//        чекать есть ли юзер
        $update = $this->update;
        $from=$update->message->from;
        $user = User::where('telegram_id',$from->id)->first();
        if (!$user) {
            $name=$from->username??$from->firstName;
            User::create(['name'=>$name, 'telegram_id'=>$from->id]);
        }
        $request = new MessageHandler();
        $request->handle($update);
    }
}
