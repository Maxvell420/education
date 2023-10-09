<?php

namespace App\Providers;

use App\Events\BotRebootEvent;
use App\Events\ChainCreateEvent;
use App\Events\ExamineStartEvent;
use App\Events\UserRegistrationEvent;
use App\Events\BotMessageEvent;
use App\Listeners\BotMessageRegisterListener;
use App\Listeners\BotRebootlistener;
use App\Listeners\EnteringModeListener;
use App\Listeners\ExamineCloseListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ExamineStartEvent::class=>[ExamineCloseListener::class],
        ChainCreateEvent::class=>[EnteringModeListener::class],
        BotRebootEvent::class=>[BotRebootlistener::class],
    ];
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {

    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
