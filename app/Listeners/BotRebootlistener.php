<?php

namespace App\Listeners;

use App\Events\BotRebootEvent;
use App\Jobs\BotUpJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BotRebootlistener
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
    public function handle(BotRebootEvent $event): void
    {
        BotUpJob::dispatch($event->update)->delay(10);
    }
}
