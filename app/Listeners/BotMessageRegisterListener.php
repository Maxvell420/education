<?php

namespace App\Listeners;

use App\Events\BotMessageEvent;
use App\Models\Bot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BotMessageRegisterListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BotMessageEvent $event): void
    {
        Bot::create(['chat_id'=>$event->message->message->chat->id]);
    }
}
