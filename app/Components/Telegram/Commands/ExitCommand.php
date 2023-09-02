<?php

namespace App\Components\Telegram\Commands;

use App\Services\BotService;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ExitCommand extends Command
{
    protected string $name = 'exit';
    protected string $description = 'deteles Chain and exit current mode';
    public function handle(): void
    {
        $botService = new BotService($this->getUpdate());
        $botService->exitMode();
        $botService->sendMessage();
        Telegram::triggerCommand('start',$this->getUpdate());
    }
}
