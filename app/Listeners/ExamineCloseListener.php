<?php

namespace App\Listeners;

use App\Events\ExamineStartEvent;
use App\Jobs\ExamineClosureJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ExamineCloseListener
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
    public function handle(ExamineStartEvent $event): void
    {
        ExamineClosureJob::dispatch($event->examine)->delay($event->seconds);
    }
}
