<?php

namespace App\Listeners;

use App\Events\ChainCreateEvent;
use App\Services\BotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Telegram\Bot\Keyboard\Keyboard;
use \Telegram\Bot\Laravel\Facades\Telegram;

class EnteringModeListener
{
    public ?int $globalwork_id;
    public BotService $botService;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(ChainCreateEvent $event): void
    {
        $this->globalwork_id = $event->collection->get('chain')->globalwork_id;
        $this->botService = $event->collection->get('bot');
        if ($this->globalwork_id!=null) {
            Telegram::sendMessage([
                'text'=>'you have entered answering mode, bellow button to exit'
                ,'chat_id'=>$this->botService->user->telegram_id
            ]);
        }
    }
}
