<?php

namespace App\Components\Telegram\Commands;

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
        $user=$this->getUpdate()->getMessage()->from;
        switch (User::where('name',$user->username)->first()){
            case !null:
                $text = 'You already started';
                break;
            case null:
                User::create(['name'=>'Mavelich','telegram_id'=>12315151]);
                $text='Hey, there! Welcome to our bot!';
                break;
        }
        $keyboard=[['text'=>'available courses','request_contact'=>true],['joined courses']];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true,]);
        Telegram::sendMessage([
            'text' => $text,
            'chat_id'=>$user->id,
            'reply_markup' => $reply_markup
        ]);
    }
}
