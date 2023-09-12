<?php

namespace App\Components\Telegram\Commands;

use App\Services\BotService;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class MenuCommand extends Command
{
    protected string $name = 'menu';
    protected string $description = 'Присылает главное меню';
    public function handle(): void
    {
        $botService = new BotService($this->getUpdate());
        $botService->handle();
    }
}
